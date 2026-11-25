<?php

namespace App\Models;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
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
}
