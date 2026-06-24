<?php

namespace App\Livewire\Front;

use App\Models\Category;
use App\Models\MenuItem;
use Livewire\Component;

class CategoryPage extends Component
{
    public Category $category;

    public function mount(string $slug)
    {
        $this->category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

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

        // 🔥 هذا هو الحدث اللي يخلي الكاونتر يتحدث
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: "تمت إضافة {$item->name} إلى السلة");
    }
    public function render()
    {
        return view('livewire.front.category-page', [
            'items' => $this->category
                ->menuItems()
                ->where('is_available', true)
                ->orderBy('sort_order')
                ->get(),
        ])->layout('layouts.app');
    }
}
