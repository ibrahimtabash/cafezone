<?php

namespace App\Models;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'image',
        'hero_image',
        'hero_image_mobile',
        'sort_order',
        'is_active',
        'is_winter',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_winter' => 'boolean',
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    /** منتجات من أقسام أخرى تظهر داخل هذا القسم أيضاً */
    public function extraItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'category_menu_item');
    }

    /** عدد كل المنتجات الظاهرة في القسم (الأصلية + المضافة) */
    public function totalItemsCount(): int
    {
        return ($this->menu_items_count ?? $this->menuItems()->count()) + ($this->extra_items_count ?? $this->extraItems()->count());
    }
}
