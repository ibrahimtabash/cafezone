<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\DeliveryAreas\DeliveryAreaResource;
use App\Filament\Resources\MenuItems\MenuItemResource;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use App\Models\Category;
use App\Models\DeliveryArea;
use App\Models\MenuItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps an administrator on the dashboard', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get('/admin')->assertOk();
});

it('lets an editor fully manage catalog delivery areas and payment methods', function () {
    $this->actingAs(User::factory()->create(['role' => 'editor']));

    $category = Category::query()->create([
        'name' => 'مشروبات',
        'slug' => 'drinks',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $menuItem = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'قهوة',
        'price' => 8,
        'is_available' => true,
        'is_featured' => false,
        'sort_order' => 1,
    ]);
    $deliveryArea = DeliveryArea::query()->create([
        'name' => 'رام الله',
        'delivery_fee' => 10,
        'is_active' => true,
    ]);
    $paymentMethod = PaymentMethod::query()->create([
        'name' => 'محفظة',
        'account_name' => 'Zone Cafe',
        'account_number' => '12345',
        'is_active' => true,
    ]);

    expect(CategoryResource::canViewAny())->toBeTrue()
        ->and(CategoryResource::canCreate())->toBeTrue()
        ->and(CategoryResource::canEdit($category))->toBeTrue()
        ->and(CategoryResource::canDelete($category))->toBeTrue()
        ->and(MenuItemResource::canEdit($menuItem))->toBeTrue()
        ->and(MenuItemResource::canDelete($menuItem))->toBeTrue()
        ->and(DeliveryAreaResource::canEdit($deliveryArea))->toBeTrue()
        ->and(DeliveryAreaResource::canDelete($deliveryArea))->toBeTrue()
        ->and(PaymentMethodResource::canEdit($paymentMethod))->toBeTrue()
        ->and(PaymentMethodResource::canDelete($paymentMethod))->toBeTrue();

    $this->get('/admin/menu-items')->assertOk();
    $this->get('/admin/categories')->assertOk();
    $this->get('/admin/delivery-areas')->assertOk();
    $this->get('/admin/payment-methods')->assertOk();
});

it('lets an editor manage orders while keeping users and tables restricted', function () {
    $editor = User::factory()->create(['role' => 'editor']);
    $this->actingAs($editor);

    expect($editor->canManageOrders())->toBeTrue();

    $this->get('/admin')->assertRedirect('/admin/menu-items');
    $this->get('/admin/orders')->assertOk();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/dining-tables')->assertForbidden();
});

it('does not grant catalog management to order staff', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]));

    expect(MenuItemResource::canViewAny())->toBeFalse()
        ->and(PaymentMethodResource::canViewAny())->toBeFalse();

    $this->get('/admin/menu-items')->assertForbidden();
})->with(['cashier', 'orders_viewer']);
