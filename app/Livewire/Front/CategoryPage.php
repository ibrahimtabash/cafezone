<?php

namespace App\Livewire\Front;

use App\Livewire\Front\Concerns\AddsToCart;
use App\Models\Category;
use App\Models\MenuItem;
use Livewire\Component;

class CategoryPage extends Component
{
    use AddsToCart;

    public Category $category;

    public function mount(string $slug)
    {
        $this->category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.front.category-page', [
            'items' => MenuItem::query()
                ->inCategory($this->category->id)
                ->where('is_available', true)
                ->latest()
                ->get(),
            'category' => $this->category,
        ])->layout('layouts.app');
    }
}
