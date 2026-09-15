<?php

use App\Events\OrderChanged;
use App\Livewire\Front\CartPage;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\OrderWorkflow;
use App\Support\CustomerOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

function pendingCafeOrder(): Order
{
    return Order::create(['order_number' => 'TEST-'.uniqid(), 'order_type' => 'takeaway',
        'subtotal' => 10, 'total' => 10, 'status' => 'pending', 'payment_status' => 'pending',
        'tracking_token' => str_repeat('a', 64)]);
}

beforeEach(function () {
    Event::fake([OrderChanged::class]);
    Storage::fake('local');
});

it('checks payment before advancing and records the complete pickup workflow', function () {
    $this->actingAs(User::factory()->create(['role' => 'cashier']));
    $order = pendingCafeOrder();
    expect(fn () => $order->update(['status' => 'preparing']))->toThrow(ValidationException::class);
    $order->refresh();
    OrderWorkflow::payment($order, true);
    $order->refresh();
    expect($order->payment_status)->toBe('confirmed')->and($order->status)->toBe('confirmed');
    foreach (['preparing', 'on_the_way', 'completed'] as $status) {
        $order->update(['status' => $status]);
    }
    expect($order->updates()->count())->toBe(5);
    expect(fn () => OrderWorkflow::payment($order, true))->toThrow(ValidationException::class);
    Event::assertDispatched(OrderChanged::class);
});

it('protects receipts and prevents a viewer from confirming payment', function () {
    $order = pendingCafeOrder();
    $order->update(['payment_receipt' => 'payment-receipts/test.jpg']);
    Storage::disk('local')->put($order->payment_receipt, 'private');
    $this->get(route('orders.receipt', $order))->assertRedirect();
    $this->actingAs(User::factory()->create(['role' => 'orders_viewer']));
    $this->get(route('orders.receipt', $order))->assertForbidden();
    expect(fn () => OrderWorkflow::payment($order, true))->toThrow(HttpException::class);
    $this->actingAs(User::factory()->create(['role' => 'cashier']));
    $this->get(route('orders.receipt', $order))->assertOk();
});

it('requires the secret tracking link and shows payment history', function () {
    $order = pendingCafeOrder();
    $this->get(route('orders.track', ['token' => str_repeat('b', 64)]))->assertNotFound();
    $this->get(route('orders.track', ['token' => $order->tracking_token]))->assertOk()->assertSee($order->order_number);
});

it('hides the active order banner while tracking that same order', function () {
    $order = pendingCafeOrder();
    $cookie = json_encode([$order->tracking_token]);

    $this->withUnencryptedCookie(CustomerOrders::COOKIE, $cookie)
        ->get(route('orders.track', ['token' => $order->tracking_token]))
        ->assertOk()
        ->assertDontSee('لديك طلب جارٍ');
});

it('creates a single order using trusted prices and a private receipt', function () {
    $category = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
    $item = MenuItem::create(['category_id' => $category->id, 'name' => 'Coffee', 'price' => 15, 'is_available' => true]);
    $method = PaymentMethod::create(['name' => 'Bank', 'account_name' => 'Cafe', 'account_number' => '123']);
    session(['cart' => [$item->id => ['id' => $item->id, 'name' => 'Fake', 'price' => 1, 'quantity' => 2, 'image' => '']], 'order_context' => ['type' => 'takeaway']]);
    $component = Livewire::test(CartPage::class)->set('customer_phone', '0599999999')
        ->set('payment_method_id', $method->id)->set('payment_receipt', UploadedFile::fake()->image('receipt.jpg'));
    $component->call('placeOrder')->assertHasNoErrors();
    $order = Order::firstOrFail();
    expect((float) $order->total)->toBe(30.0)->and($order->payment_status)->toBe('pending');
    Storage::disk('local')->assertExists($order->payment_receipt);
    $component->call('placeOrder')->assertHasNoErrors();
    expect(Order::count())->toBe(1);
});

it('does not accept missing proof or disabled payment methods', function () {
    session(['cart' => [['id' => 1, 'price' => 10, 'quantity' => 1, 'name' => 'Test', 'image' => '']], 'order_context' => ['type' => 'takeaway']]);
    $method = PaymentMethod::create(['name' => 'Bank', 'account_name' => 'Cafe', 'account_number' => '123', 'is_active' => false]);
    Livewire::test(CartPage::class)->set('customer_phone', '0599999999')->set('payment_method_id', $method->id)
        ->call('placeOrder')->assertHasErrors(['payment_method_id', 'payment_receipt']);
    expect(Order::count())->toBe(0);
});

it('renders cashier order pages and marks an opened order read', function () {
    $order = pendingCafeOrder();
    $this->actingAs(User::factory()->create(['role' => 'cashier']));
    $this->get('/admin/orders')->assertOk();
    $this->get('/admin/orders/'.$order->id)->assertOk();
    expect($order->fresh()->opened_at)->not->toBeNull();
    $this->get('/admin/payment-methods')->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->get('/admin/payment-methods')->assertOk();
});

it('prints a protected invoice with order details', function () {
    $order = pendingCafeOrder();
    $order->items()->create(['name' => 'قهوة', 'price' => 10, 'quantity' => 1, 'total' => 10]);

    $this->get(route('orders.print', $order))->assertRedirect();
    $this->actingAs(User::factory()->create(['role' => 'orders_viewer']))
        ->get(route('orders.print', $order))->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'cashier']))
        ->get(route('orders.print', $order))->assertOk()
        ->assertSee($order->order_number)->assertSee('قهوة')->assertSee('طباعة الفاتورة');
});

it('includes a reason for rejected proof without advancing the order', function () {
    $this->actingAs(User::factory()->create(['role' => 'cashier']));
    $order = pendingCafeOrder();
    OrderWorkflow::payment($order, false, 'الصورة غير واضحة');
    $order->refresh();
    expect($order->status)->toBe('pending')->and($order->payment_status)->toBe('rejected');
    expect($order->updates()->latest('id')->first()->message)->toContain('الصورة غير واضحة');
});

it('lets a cashier roll an order back one stage at a time', function () {
    $this->actingAs(User::factory()->create(['role' => 'cashier']));
    $order = pendingCafeOrder();
    OrderWorkflow::payment($order, true);
    $order->refresh();
    OrderWorkflow::advance($order, 'preparing');
    OrderWorkflow::advance($order, 'on_the_way');

    OrderWorkflow::rollBack($order);
    expect($order->fresh()->status)->toBe('preparing');
    OrderWorkflow::rollBack($order);
    expect($order->fresh()->status)->toBe('confirmed');
    OrderWorkflow::rollBack($order);
    $order->refresh();
    expect($order->status)->toBe('pending')
        ->and($order->payment_status)->toBe('pending')
        ->and($order->payment_confirmed_at)->toBeNull();
});

it('lets a cashier select a distant valid status directly', function () {
    $this->actingAs(User::factory()->create(['role' => 'cashier']));
    $order = pendingCafeOrder();
    OrderWorkflow::payment($order, true);
    $order->refresh();
    OrderWorkflow::setStatus($order, 'completed');
    expect($order->fresh()->status)->toBe('completed');

    OrderWorkflow::setStatus($order, 'confirmed');
    expect($order->fresh()->status)->toBe('confirmed');
});
