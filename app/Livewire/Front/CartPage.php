<?php

namespace App\Livewire\Front;

use App\Models\DeliveryArea;
use App\Models\DiningTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Support\CustomerOrders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CartPage extends Component
{
    use WithFileUploads;

    public $payment_receipt;

    public ?int $payment_method_id = null;

    #[Locked]
    public string $checkout_key;

    public array $cart = [];

    public bool $orderSuccess = false;

    public bool $showCheckout = false;

    public string $customer_name = '';

    public string $customer_phone = '';

    public string $address = '';

    public ?int $delivery_area_id = null;

    public string $notes = '';

    public float $delivery_fee = 0;

    public string $order_type = 'delivery';

    public ?int $dining_table_id = null;

    public ?string $dining_table_name = null;

    // public float $total = 0;

    public function mount()
    {
        $this->checkout_key = session('checkout_key', (string) Str::uuid());
        session(['checkout_key' => $this->checkout_key]);
        $this->cart = session('cart', []);
        $context = session('order_context', ['type' => 'delivery']);
        $this->order_type = $context['type'] ?? 'delivery';
        $this->dining_table_id = $context['table_id'] ?? null;
        $this->dining_table_name = $context['table_name'] ?? null;
    }

    public function increase($itemId): void
    {
        $cart = session('cart', []);

        if (isset($cart[$itemId])) {
            $cart[$itemId]['quantity']++;
        }

        $this->syncCart($cart);
    }

    public function decrease($itemId): void
    {
        $cart = session('cart', []);

        if (isset($cart[$itemId])) {
            $cart[$itemId]['quantity']--;

            if ($cart[$itemId]['quantity'] <= 0) {
                unset($cart[$itemId]);
            }
        }

        $this->syncCart($cart);
    }

    public function remove($itemId): void
    {
        $cart = session('cart', []);

        unset($cart[$itemId]);

        $this->syncCart($cart);
    }

    public function openCheckout(): void
    {
        $this->cart = session('cart', []);

        if ($this->cart === []) {
            $this->addError('cart', 'السلة فارغة. أضف منتجاً قبل إتمام الطلب.');

            return;
        }

        $this->resetValidation();
        $this->showCheckout = true;
        $this->dispatch('checkout-opened');
    }

    public function backToCart(): void
    {
        $this->showCheckout = false;
        $this->resetValidation();
    }

    /**
     * 🔥 مركز التحكم بالسلة
     */
    private function syncCart(array $cart): void
    {
        session(['cart' => $cart]);
        $this->cart = $cart;

        // 🔥 لازم نفس event اللي يستخدمه CartCounter
        $this->dispatch('cart-updated');
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(
            fn ($item) => $item['price'] * $item['quantity']
        );
    }

    //
    public function getDeliveryFeeProperty()
    {
        if ($this->order_type !== 'delivery') {
            return 0;
        }
        if (! $this->delivery_area_id) {
            return 0;
        }

        $area = DeliveryArea::find($this->delivery_area_id);

        return $area?->delivery_fee ?? 0;
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->delivery_fee;
    }

    public function setDeliveryArea($value)
    {
        $this->delivery_area_id = $value;

        if (! $value) {
            $this->delivery_fee = 0;

            return;
        }

        $area = DeliveryArea::find($value);

        $this->delivery_fee = $area?->delivery_fee ?? 0;

    }

    public function placeOrder()
    {
        $existing = Order::where('checkout_key', $this->checkout_key)->first();
        if ($existing) {
            return $this->redirectRoute('orders.track', ['token' => $existing->tracking_token]);
        }
        $this->cart = session('cart', []);
        $context = session('order_context', ['type' => 'delivery']);
        $this->order_type = $context['type'] ?? 'delivery';
        $this->dining_table_id = $context['table_id'] ?? null;
        $this->validate([
            'cart' => 'required|array|min:1',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => ['required', 'string', 'regex:/^[+0-9 ()-]{6,30}$/'],
            'notes' => 'nullable|string|max:1000',
            'payment_method_id' => ['required', Rule::exists('payment_methods', 'id')->where('is_active', true)],
            'payment_receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'delivery_area_id' => $this->order_type === 'delivery' ? ['required', Rule::exists('delivery_areas', 'id')->where('is_active', true)] : 'nullable',
            'address' => $this->order_type === 'delivery' ? 'required|string|min:5|max:500' : 'nullable',
        ]);

        $rateLimitKey = 'place-order:'.request()->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'cart' => 'تم إرسال عدة طلبات خلال وقت قصير. حاول مجدداً بعد '.RateLimiter::availableIn($rateLimitKey).' ثانية.',
            ]);
        }
        RateLimiter::hit($rateLimitKey, 60);

        $receipt = $this->payment_receipt->store('payment-receipts', 'local');
        try {
            $order = DB::transaction(function () use ($receipt) {
                $existing = Order::where('checkout_key', $this->checkout_key)->first();
                if ($existing) {
                    return $existing;
                }
                $method = PaymentMethod::where('is_active', true)->findOrFail($this->payment_method_id);
                $items = [];
                $subtotal = 0;
                foreach ($this->cart as $item) {
                    $product = MenuItem::where('is_available', true)->find($item['id']);
                    $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
                    if (! $product || ! $quantity || $quantity < 1 || $quantity > 99) {
                        throw ValidationException::withMessages(['cart' => 'أحد الأصناف غير متاح أو الكمية غير صالحة. حدّث السلة.']);
                    }
                    $line = (int) round((float) $product->price * 100) * $quantity;
                    $subtotal += $line;
                    $items[] = ['menu_item_id' => $product->id, 'name' => $product->name, 'price' => $product->price, 'quantity' => $quantity, 'total' => $line / 100];
                }
                $fee = $this->order_type === 'delivery' ? DeliveryArea::where('is_active', true)->findOrFail($this->delivery_area_id)->delivery_fee : 0;
                if ($this->order_type === 'dine_in') {
                    DiningTable::where('is_active', true)->findOrFail($this->dining_table_id);
                }
                $order = Order::create([
                    'checkout_key' => $this->checkout_key,
                    'tracking_token' => Str::random(64), 'order_type' => $this->order_type,
                    'dining_table_id' => $this->order_type === 'dine_in' ? $this->dining_table_id : null,
                    'customer_name' => $this->customer_name ?: null, 'customer_phone' => $this->customer_phone,
                    'delivery_area_id' => $this->order_type === 'delivery' ? $this->delivery_area_id : null,
                    'address' => $this->order_type === 'delivery' ? $this->address : null,
                    'subtotal' => $subtotal / 100, 'delivery_fee' => $fee, 'total' => ($subtotal + (int) round($fee * 100)) / 100,
                    'notes' => $this->notes, 'status' => 'pending', 'payment_status' => 'pending',
                    'payment_method_id' => $method->id, 'payment_receipt' => $receipt,
                    'payment_details' => $method->only(['name', 'account_name', 'account_number', 'instructions']),
                ]);
                $order->items()->createMany($items);

                return $order;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($receipt);
            // A simultaneous retry may already have committed this checkout.
            $order = Order::where('checkout_key', $this->checkout_key)->first();
            if (! $order) {
                throw $e;
            }
        }
        if ($order->payment_receipt !== $receipt) {
            Storage::disk('local')->delete($receipt);
        }
        session()->forget(['cart', 'checkout_key']);
        CustomerOrders::remember($order);
        $this->dispatch('cart-updated');

        return $this->redirectRoute('orders.track', ['token' => $order->tracking_token]);
    }

    public function render()
    {
        return view('livewire.front.cart-page', [
            'areas' => DeliveryArea::where('is_active', true)->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
        ])->layout('layouts.app');
    }
}
