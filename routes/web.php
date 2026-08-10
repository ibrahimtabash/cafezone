<?php

use App\Livewire\Front\CartPage;
use App\Livewire\Front\CategoryPage;
use App\Livewire\Front\HomePage;
use App\Models\DiningTable;
use App\Http\Controllers\Admin\ExportMenuItemsController;
use App\Http\Controllers\Admin\ExportDiningTablesController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('index');
// });

Route::get('/', HomePage::class)->name('home');
Route::get('/category/{slug}', CategoryPage::class)
    ->name('category.show');
Route::get('/cart', CartPage::class)
    ->name('cart.index');

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
