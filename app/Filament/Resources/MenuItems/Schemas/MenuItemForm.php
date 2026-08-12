<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Support\CompressedImageUploader;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('السعر')
                    ->required()
                    ->numeric()
                    ->suffix('₪'),
                FileUpload::make('image')
                    ->label('الصورة')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->disk('public')
                    ->directory('items')
                    ->maxSize(10240)
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('1600')
                    ->imageResizeUpscale(false)
                    ->saveUploadedFileUsing(fn ($file): string => CompressedImageUploader::store($file, 'items'))
                    ->helperText('تُضغط الصورة تلقائيًا وتُحفظ بصيغة WebP. الحد الأقصى قبل الضغط 10MB.'),
                Toggle::make('is_available')
                    ->label('متاح للطلب')
                    ->required(),
                Toggle::make('is_featured')
                    ->label('منتج مميز')
                    ->required(),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
