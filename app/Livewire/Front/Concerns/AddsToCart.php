<?php

namespace App\Livewire\Front\Concerns;

use App\Models\MenuItem;

trait AddsToCart
{
    public function addToCart(int $itemId): void
    {
        $item = MenuItem::findOrFail($itemId);

        $cart = session()->get('cart', []);

        if (isset($cart[$item->id])) {
            $cart[$item->id]['quantity']++;
        } else {
            $cart[$item->id] = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'image' => $item->image,
                'quantity' => 1,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: "تمت إضافة {$item->name} إلى السلة");
    }
}
