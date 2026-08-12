<?php

namespace App\Filament\Resources\DeliveryAreas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المنطقة')
                    ->required(),
                TextInput::make('delivery_fee')
                    ->label('رسوم التوصيل')
                    ->required()
                    ->numeric()
                    ->suffix('₪')
                    ->default(0.0),
                Toggle::make('is_active')
                    ->label('نشطة')
                    ->required(),
            ]);
    }
}
