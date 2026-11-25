<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Support\CompressedImageUploader;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم التصنيف')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, ?string $old, Get $get, Set $set, string $operation): void {
                        // تعبئة تلقائية عند الإنشاء فقط، وبشرط أن المستخدم لم يعدّل المعرّف يدوياً
                        if ($operation !== 'create' || ($get('slug') ?? '') !== Str::slug($old ?? '')) {
                            return;
                        }

                        $set('slug', Str::slug($state ?? ''));
                    }),
                TextInput::make('slug')
                    ->label('معرّف الرابط')
                    ->helperText('يُعبّأ تلقائياً من الاسم ويمكنك تعديله. يُستخدم داخل رابط صفحة التصنيف، مثل: hot-drinks')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('الوصف'),
                FileUpload::make('image')
                    ->label('الصورة')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->disk('public')
                    ->directory('categories')
                    ->maxSize(10240)
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('1600')
                    ->imageResizeUpscale(false)
                    ->saveUploadedFileUsing(fn ($file): string => CompressedImageUploader::store($file, 'categories'))
                    ->helperText('تُضغط الصورة تلقائيًا وتُحفظ بصيغة WebP. الحد الأقصى قبل الضغط 10MB.'),
                FileUpload::make('hero_image')
                    ->label('صورة هيرو الصفحة (ديسكتوب) - اختيارية')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->disk('public')
                    ->directory('categories')
                    ->maxSize(10240)
                    ->saveUploadedFileUsing(fn ($file): string => CompressedImageUploader::store($file, 'categories'))
                    ->helperText('إن تُركت فارغة تُستخدم صورة التصنيف.'),
                FileUpload::make('hero_image_mobile')
                    ->label('صورة هيرو الصفحة (جوال) - اختيارية')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->disk('public')
                    ->directory('categories')
                    ->maxSize(10240)
                    ->saveUploadedFileUsing(fn ($file): string => CompressedImageUploader::store($file, 'categories'))
                    ->helperText('إن تُركت فارغة تُستخدم صورة الديسكتوب.'),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->required(),
                Select::make('extraItems')
                    ->label('منتجات من أقسام أخرى')
                    ->helperText('أضف منتجات موجودة في أقسام أخرى لتظهر هنا أيضاً مع قسمها الأصلي (مثلاً لقسم شتوي).')
                    ->relationship(
                        'extraItems',
                        'name',
                        // لا تظهر منتجات هذا القسم الأصلية في الخيارات
                        modifyQueryUsing: fn (Builder $query, ?Model $record) => $record
                            ? $query->where('menu_items.category_id', '!=', $record->getKey())
                            : $query,
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Toggle::make('is_winter')
                    ->label('قسم شتوي')
                    ->helperText('يظهر هذا التصنيف داخل صفحة المنتجات الشتوية التي يفتحها البانر الشتوي.')
                    ->default(false),
            ]);
    }
}
