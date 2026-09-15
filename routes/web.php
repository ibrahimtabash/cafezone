<?php

use App\Http\Controllers\Admin\ExportDiningTablesController;
use App\Http\Controllers\Admin\ExportMenuItemsController;
use App\Livewire\Front\CartPage;
use App\Livewire\Front\CategoryPage;
use App\Livewire\Front\HomePage;
use App\Livewire\Front\MyOrders;
use App\Livewire\Front\TrackOrder;
use App\Models\DiningTable;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('index');
// });

Route::get('/', HomePage::class)->name('home');
Route::get('/category/{slug}', CategoryPage::class)
    ->name('category.show');
Route::get('/cart', CartPage::class)
    ->name('cart.index');

Route::get('/my-orders', MyOrders::class)->name('orders.mine');

Route::get('/orders/track/{token}', TrackOrder::class)
    ->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:120,1')->name('orders.track');

Route::get('/admin/orders/{order}/receipt', function (Order $order) {
    abort_unless(auth()->user()?->canManageOrders(), 403);
    abort_unless($order->payment_receipt && Storage::disk('local')->exists($order->payment_receipt), 404);

    return Storage::disk('local')->response($order->payment_receipt, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
})->middleware('auth')->name('orders.receipt');

Route::get('/admin/print-order/{order}', function (Order $order) {
    abort_unless(auth()->user()?->canManageOrders(), 403);

    return response()->view('admin.order-invoice', [
        'order' => $order->load(['items', 'diningTable', 'deliveryArea']),
    ])->header('Cache-Control', 'private, no-store');
})->middleware('auth')->name('orders.print');

Route::get('/menu/table/{table:code}', function (DiningTable $table) {
    abort_unless($table->is_active, 404);
    session(['order_context' => ['type' => 'dine_in', 'table_id' => $table->id, 'table_name' => $table->name]]);

    return redirect()->route('home');
})->name('menu.table');

Route::get('/menu/takeaway', function () {
    session(['order_context' => ['type' => 'takeaway']]);

    return redirect()->route('home');
})->name('menu.takeaway');

Route::get('/qr/table/{table}', function (DiningTable $table) {
    return view('qr', ['title' => $table->name, 'url' => route('menu.table', $table)]);
})->name('qr.table');

Route::get('/qr/takeaway', fn () => view('qr', [
    'title' => 'طلبات خارجية',
    'url' => route('menu.takeaway'),
]))->name('qr.takeaway');

Route::get('/admin/menu-items/export/excel', ExportMenuItemsController::class)
    ->middleware('auth')
    ->name('admin.menu-items.export');

Route::get('/admin/dining-tables/export/excel', ExportDiningTablesController::class)
    ->middleware('auth')
    ->name('admin.dining-tables.export');
