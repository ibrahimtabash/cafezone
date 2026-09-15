<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Services\OrderWorkflow;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('الإجراء والحالة')
            ->recordClasses(fn (Order $record) => $record->opened_at ? null : 'order-unread')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('opened_at')->label('المشاهدة')->badge()->formatStateUsing(fn ($state) => 'تم الفتح')->placeholder('● جديد — لم يفتح')->color('warning')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_status')->label('الدفع')->badge()->formatStateUsing(fn ($state) => ['pending' => 'بانتظار تأكيد الكاشير', 'confirmed' => 'مؤكد', 'rejected' => 'مرفوض', 'unpaid' => 'غير مدفوع'][$state] ?? $state)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->description(fn (Order $record) => $record->opened_at ? null : '● طلب جديد')
                    ->color(fn (Order $record) => $record->opened_at ? null : 'warning')
                    ->searchable(),
                TextColumn::make('order_type')->label('نوع الطلب')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'dine_in' => 'داخل الكافي', 'takeaway' => 'خارجي', default => 'توصيل',
                })->color(fn (string $state) => match ($state) {
                    'dine_in' => 'success', 'takeaway' => 'warning', default => 'info',
                }),
                TextColumn::make('diningTable.name')->label('الطاولة')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_name')
                    ->label('العميل / الموقع')->placeholder('زبون الطاولة')
                    ->formatStateUsing(fn (?string $state, Order $record) => $record->order_type === 'dine_in' ? 'طاولة: '.($record->diningTable?->name ?? '—') : ($state ?: 'بدون اسم'))
                    ->description(fn (Order $record) => $record->order_type === 'delivery' ? ($record->deliveryArea?->name ?? $record->customer_phone) : $record->customer_phone)
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('الهاتف')->placeholder('—')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deliveryArea.name')
                    ->label('منطقة التوصيل')->placeholder('—')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subtotal')
                    ->label('المجموع')
                    ->numeric()
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivery_fee')
                    ->label('التوصيل')
                    ->numeric()
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')
                    ->label('الإجمالي')->suffix(' ₪')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->wrap()
                    ->formatStateUsing(fn (Order $record) => $record->statusMessage())
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning', 'confirmed' => 'info', 'preparing' => 'warning',
                        'on_the_way', 'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                    }),
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
                SelectFilter::make('status')->label('الحالة')->options(Order::statusLabels()),
            ])
            ->recordActions([
                Action::make('confirmPayment')->label('تأكيد الدفع')->icon('heroicon-o-check-badge')->color('success')->button()
                    ->visible(fn (Order $record) => auth()->user()?->canManageOrders() && $record->status === 'pending' && in_array($record->payment_status, ['pending', 'rejected', 'unpaid']))
                    ->requiresConfirmation()->modalHeading('تأكيد استلام الدفع')->modalDescription('بعد التأكيد سينتقل الطلب مباشرة إلى قائمة الانتظار ويصل إشعار للزبون.')
                    ->action(function (Order $record, Action $action) {
                        OrderWorkflow::payment($record, true);
                        $action->success();
                    })->successNotificationTitle('تم تأكيد الدفع وأُبلغ الزبون'),
                Action::make('startPreparing')->label('بدء التجهيز')->icon('heroicon-o-fire')->color('warning')->button()
                    ->visible(fn (Order $record) => auth()->user()?->canManageOrders() && $record->status === 'confirmed')
                    ->action(function (Order $record, Action $action) {
                        OrderWorkflow::advance($record, 'preparing');
                        $action->success();
                    })->successNotificationTitle('انتقل الطلب إلى المطبخ'),
                Action::make('markReady')->label(fn (Order $record) => $record->order_type === 'delivery' ? 'إرساله للطريق' : 'جاهز للاستلام')
                    ->icon('heroicon-o-bell-alert')->color('info')->button()
                    ->visible(fn (Order $record) => auth()->user()?->canManageOrders() && $record->status === 'preparing')
                    ->action(function (Order $record, Action $action) {
                        OrderWorkflow::advance($record, 'on_the_way');
                        $action->success();
                    })->successNotificationTitle('تم إشعار الزبون بأن الطلب جاهز'),
                Action::make('complete')->label('تم الاستلام')->icon('heroicon-o-check-circle')->color('success')->button()
                    ->visible(fn (Order $record) => auth()->user()?->canManageOrders() && $record->status === 'on_the_way')
                    ->requiresConfirmation()->modalHeading('إغلاق الطلب؟')->modalDescription('أكد بعد تسليم الطلب للزبون.')
                    ->action(function (Order $record, Action $action) {
                        OrderWorkflow::advance($record, 'completed');
                        $action->success();
                    })->successNotificationTitle('تم إكمال الطلب'),
                Action::make('finishedState')->label(fn (Order $record) => $record->status === 'cancelled' ? 'طلب ملغي' : 'طلب مكتمل')
                    ->icon(fn (Order $record) => $record->status === 'cancelled' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Order $record) => $record->status === 'cancelled' ? 'danger' : 'gray')->button()->disabled()
                    ->visible(fn (Order $record) => in_array($record->status, ['completed', 'cancelled'])),
                ActionGroup::make([
                    self::statusAction('setPending', 'pending', 'بانتظار تأكيد الدفع', 'heroicon-o-clock', 'warning'),
                    self::statusAction('setConfirmed', 'confirmed', 'تم الدفع — في الانتظار', 'heroicon-o-check-badge', 'info'),
                    self::statusAction('setPreparing', 'preparing', 'قيد التجهيز', 'heroicon-o-fire', 'warning'),
                    self::statusAction('setReady', 'on_the_way', 'جاهز / في الطريق', 'heroicon-o-bell-alert', 'success'),
                    self::statusAction('setCompleted', 'completed', 'تم الاستلام', 'heroicon-o-check-circle', 'success'),
                    self::statusAction('setCancelled', 'cancelled', 'إلغاء الطلب', 'heroicon-o-x-circle', 'danger'),
                    EditAction::make()->label('تعديل كامل')->icon('heroicon-o-pencil-square'),
                ])->icon('heroicon-o-chevron-down')->iconButton()->tooltip('اختيار الحالة مباشرة'),
                Action::make('receipt')->label('إثبات الدفع')->icon('heroicon-o-photo')
                    ->visible(fn (Order $record) => auth()->user()?->canManageOrders() && filled($record->payment_receipt) && $record->status === 'pending')
                    ->modalHeading('معاينة إثبات الدفع')
                    ->modalContent(fn (Order $record) => view('admin.payment-receipt-preview', ['order' => $record]))
                    ->modalSubmitAction(false)->modalCancelActionLabel('إغلاق'),
                ViewAction::make()->label('التفاصيل')->icon('heroicon-o-eye')->iconButton()->tooltip('عرض التفاصيل'),
                Action::make('print')->label('طباعة')->icon('heroicon-o-printer')->iconButton()->tooltip('طباعة الفاتورة')
                    ->visible(fn () => auth()->user()?->canManageOrders())
                    ->url(fn (Order $record) => route('orders.print', $record))->openUrlInNewTab(),
                Action::make('whatsapp')->label('واتساب')->icon('heroicon-o-chat-bubble-left-right')->iconButton()->tooltip('فتح واتساب')
                    ->visible(fn (Order $record) => auth()->user()?->isAdmin() === true && filled($record->customer_phone))
                    ->url(function (Order $record): string {
                        $phone = preg_replace('/\D+/', '', $record->customer_phone);
                        $message = "مرحباً {$record->customer_name}، تحديث طلبك رقم {$record->order_number}: ";

                        return "https://wa.me/{$phone}?text=".urlencode($message);
                    })->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function statusAction(string $name, string $status, string $label, string $icon, string $color): Action
    {
        return Action::make($name)->label($label)->icon($icon)->color($color)
            ->visible(fn (Order $record) => auth()->user()?->canManageOrders()
                && $record->status !== $status
                && (in_array($status, ['pending', 'cancelled']) || $record->payment_status === 'confirmed'))
            ->requiresConfirmation(in_array($status, ['pending', 'cancelled']))
            ->modalDescription($status === 'pending' ? 'سيتم إلغاء تأكيد الدفع وإعادة الطلب للمراجعة.' : null)
            ->action(function (Order $record, Action $action) use ($status) {
                OrderWorkflow::setStatus($record, $status);
                $action->success();
            })->successNotificationTitle('تم تغيير حالة الطلب وإبلاغ الزبون');
    }
}
