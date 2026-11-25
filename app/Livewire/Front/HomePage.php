<?php

namespace App\Livewire\Front;

use App\Models\Category;
use App\Models\SiteSetting;
use Livewire\Component;

class HomePage extends Component
{
    public function render()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        return view('livewire.front.home-page', [
            'categories' => $categories,
            'hero' => SiteSetting::hero('home'),
            'hasWinter' => $categories->contains(fn ($c) => $c->is_winter),
        ])->layout('layouts.app');
    }
}
