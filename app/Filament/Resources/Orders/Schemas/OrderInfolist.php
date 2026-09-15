<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات الطلب')->schema([
                TextEntry::make('order_number')->label('رقم الطلب')->copyable(),
                TextEntry::make('order_type')->label('نوع الطلب')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'dine_in' => 'داخل الكافي',
                    'takeaway' => 'طلب خارجي / استلام',
                    default => 'توصيل',
                }),
                TextEntry::make('status')->label('الحالة')->badge()->formatStateUsing(fn (Order $record) => $record->statusMessage()),
                TextEntry::make('created_at')->label('وقت الطلب')->dateTime('Y-m-d h:i A'),
                TextEntry::make('diningTable.name')->label('الطاولة')->placeholder('—'),
                TextEntry::make('customer_name')->label('اسم العميل')->placeholder('زبون الطاولة'),
                TextEntry::make('customer_phone')->label('رقم الهاتف')->placeholder('—')->copyable(),
                TextEntry::make('deliveryArea.name')->label('منطقة التوصيل')->placeholder('—'),
                TextEntry::make('address')->label('العنوان')->placeholder('—')->columnSpanFull(),
                TextEntry::make('notes')->label('ملاحظة العميل')->placeholder('لا توجد ملاحظات')->columnSpanFull(),
            ])->columns(3),

            Section::make('تفاصيل الأصناف')->schema([
                RepeatableEntry::make('items')->label('')->schema([
                    TextEntry::make('name')->label('الصنف'),
                    TextEntry::make('price')->label('سعر الوحدة')->money('ILS'),
                    TextEntry::make('quantity')->label('الكمية'),
                    TextEntry::make('total')->label('الإجمالي')->money('ILS'),
                ])->columns(4)->columnSpanFull(),
            ]),

            Section::make('الحساب')->schema([
                TextEntry::make('payment_details.name')->label('طريقة الدفع'),
                TextEntry::make('payment_details.account_name')->label('صاحب الحساب'),
                TextEntry::make('payment_details.account_number')->label('رقم الحساب / المحفظة'),
                TextEntry::make('payment_status')->label('حالة الدفع')->formatStateUsing(fn ($state) => ['pending' => 'بانتظار تأكيد الكاشير', 'confirmed' => 'مؤكد', 'rejected' => 'مرفوض', 'unpaid' => 'غير مدفوع'][$state] ?? $state),
                TextEntry::make('payment_note')->label('ملاحظة الدفع'),
                TextEntry::make('payment_confirmed_at')->label('وقت تأكيد الدفع')->dateTime(),
                TextEntry::make('subtotal')->label('مجموع الأصناف')->money('ILS'),
                TextEntry::make('delivery_fee')->label('رسوم التوصيل')->money('ILS'),
                TextEntry::make('total')->label('الإجمالي النهائي')->money('ILS')->weight('bold'),
            ])->columns(3),
        ]);
    }
}
