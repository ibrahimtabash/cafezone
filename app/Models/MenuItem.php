<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** أقسام إضافية يظهر فيها المنتج (مثل القسم الشتوي) غير قسمه الأصلي */
    public function extraCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_menu_item');
    }

    /** منتجات قسم: التي تتبعه أصلاً + المضافة إليه من أقسام أخرى */
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('category_id', $categoryId)
            ->orWhereHas('extraCategories', fn (Builder $c) => $c->whereKey($categoryId)));
    }
}
