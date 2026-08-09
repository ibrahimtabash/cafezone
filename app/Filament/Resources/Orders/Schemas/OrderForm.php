<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('رقم الطلب')
                    ->required(),
                Select::make('order_type')->label('نوع الطلب')->options([
                    'dine_in' => 'داخل الكافي', 'takeaway' => 'طلب خارجي', 'delivery' => 'توصيل',
                ])->required(),
                Select::make('dining_table_id')->label('الطاولة')->relationship('diningTable', 'name'),
                TextInput::make('customer_name')
                    ->label('اسم العميل'),
                TextInput::make('customer_phone')
                    ->label('رقم الهاتف')->tel(),
                Select::make('delivery_area_id')
                    ->relationship('deliveryArea', 'name')
                    ->label('منطقة التوصيل'),
                Textarea::make('address')
                    ->label('العنوان')
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->label('المجموع')
                    ->required()
                    ->numeric()
                    ->suffix('₪'),
                TextInput::make('delivery_fee')
                    ->label('رسوم التوصيل')
                    ->required()
                    ->numeric()
                    ->suffix('₪')
                    ->default(0.0),
                TextInput::make('total')
                    ->label('الإجمالي')
                    ->required()
                    ->numeric()
                    ->suffix('₪'),
                Textarea::make('notes')
                    ->label('ملاحظة الطلب')
                    ->helperText('يمكن كتابة أو تعديل الملاحظة لجميع أنواع الطلبات، بما فيها طلبات الطاولات داخل الكافي.')
                    ->rows(4)
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
            'pending' => 'جديد',
            'confirmed' => 'مؤكد',
            'preparing' => 'قيد التحضير',
            'on_the_way' => 'في الطريق / جاهز',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
