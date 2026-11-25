<?php

namespace App\Livewire\Front;

use App\Models\Category;
use App\Models\SiteSetting;
use Livewire\Component;

class WinterPage extends Component
{
    public function mount(): void
    {
        abort_unless(config('cafe.winter_theme'), 404);
    }

    public function render()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->where('is_winter', true)
            ->withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('livewire.front.winter-page', [
            'categories' => $categories,
            'hero' => SiteSetting::hero('winter'),
        ])->layout('layouts.app');
    }
}
