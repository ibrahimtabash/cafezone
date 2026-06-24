<?php

use App\Livewire\Front\CartPage;
use App\Livewire\Front\CategoryPage;
use App\Livewire\Front\HomePage;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('index');
// });

Route::get('/', HomePage::class);
Route::get('/category/{slug}', CategoryPage::class)
    ->name('category.show');
Route::get('/cart', CartPage::class)
    ->name('cart.index');
