<?php

namespace App\Livewire\Front;

use Livewire\Attributes\On;
use Livewire\Component;

class CartCounter extends Component
{
    #[On('cart-updated')]
    public function refreshCart()
    {
        // فقط لإجبار إعادة الـ render
    }

    public function render()
    {
        $count = collect(session('cart', []))
            ->sum('quantity');

        return view('livewire.front.cart-counter', [
            'count' => $count,
        ]);
    }
}
