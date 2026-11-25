<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    public const PAGES = ['home' => 'الصفحة الرئيسية', 'winter' => 'الصفحة الشتوية'];

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * صور هيرو صفحة: [ديسكتوب, جوال].
     * الأولوية: صورة مرفوعة للصفحة، ثم (للشتوية) صورة قسم مختار، ثم الصورة الثابتة الافتراضية.
     * صورة الجوال اختيارية وتعود لصورة الديسكتوب إن لم تُحدَّد.
     */
    public static function hero(string $page): array
    {
        $default = asset('assets/images/hero-image.webp');

        $desktop = static::url(static::get("{$page}_hero_image"));

        if (! $desktop && $page === 'winter' && ($categoryId = static::get('winter_hero_category'))) {
            $desktop = static::url(Category::query()->whereKey($categoryId)->value('image'));
        }

        $desktop ??= $default;
        $mobile = static::url(static::get("{$page}_hero_mobile")) ?? $desktop;

        return [$desktop, $mobile];
    }

    public static function url(?string $path): ?string
    {
        return filled($path) ? Storage::disk('public')->url($path) : null;
    }
}
