<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Support\CompressedImageUploader;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم التصنيف')
                    ->required(),
                TextInput::make('slug')
                    ->label('معرّف الرابط')
                    ->helperText('يُستخدم داخل رابط صفحة التصنيف، مثل: hot-drinks')
                    ->required(),
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
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->required(),
            ]);
    }
}
