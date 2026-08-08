<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\Order;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable(),
                TextColumn::make('order_type')->label('نوع الطلب')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'dine_in' => 'داخل الكافي', 'takeaway' => 'خارجي', default => 'توصيل',
                })->color(fn (string $state) => match ($state) {
                    'dine_in' => 'success', 'takeaway' => 'warning', default => 'info',
                }),
                TextColumn::make('diningTable.name')->label('الطاولة')->placeholder('—'),
                TextColumn::make('customer_name')
                    ->label('العميل')->placeholder('زبون الطاولة')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('الهاتف')->placeholder('—')
                    ->searchable(),
                TextColumn::make('deliveryArea.name')
                    ->label('منطقة التوصيل')->placeholder('—')
                    ->searchable(),
                TextColumn::make('subtotal')
                    ->label('المجموع')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_fee')
                    ->label('التوصيل')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->label('الإجمالي')->suffix(' ₪')
                    ->numeric()
                    ->sortable(),
                SelectColumn::make('status')->label('الحالة')->options([
                    'pending' => 'جديد', 'confirmed' => 'مؤكد', 'preparing' => 'قيد التحضير',
                    'on_the_way' => 'في الطريق / جاهز', 'completed' => 'مكتمل', 'cancelled' => 'ملغي',
                ])->selectablePlaceholder(false),
                TextColumn::make('created_at')
                    ->label('وقت الطلب')->dateTime('Y-m-d h:i A')
                    ->sortable()
                    ->since(),
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_type')->label('نوع الطلب')->options([
                    'dine_in' => 'داخل الكافي', 'takeaway' => 'خارجي', 'delivery' => 'توصيل',
                ]),
                SelectFilter::make('status')->label('الحالة')->options([
                    'pending' => 'جديد', 'confirmed' => 'مؤكد', 'preparing' => 'قيد التحضير',
                    'on_the_way' => 'في الطريق / جاهز', 'completed' => 'مكتمل', 'cancelled' => 'ملغي',
                ]),
            ])
            ->recordActions([
                ViewAction::make()->label('التفاصيل'),
                Action::make('whatsapp')->label('واتساب')->icon('heroicon-o-chat-bubble-left-right')
                    ->visible(fn (Order $record) => filled($record->customer_phone))
                    ->url(function (Order $record): string {
                        $phone = preg_replace('/\D+/', '', $record->customer_phone);
                        $message = "مرحباً {$record->customer_name}، تحديث طلبك رقم {$record->order_number}: ";
                        return "https://wa.me/{$phone}?text=" . urlencode($message);
                    })->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
