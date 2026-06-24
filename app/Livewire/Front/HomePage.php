<?php

namespace App\Livewire\Front;

use App\Models\Category;
use Livewire\Component;

class HomePage extends Component
{
    public function render()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('menuItems')
            ->orderBy('sort_order')
            ->get();
        return view('livewire.front.home-page', [
            'categories' => $categories,
        ])->layout('layouts.app');
    }
}
