<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderWorkflow;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\On;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        if (! $this->record->opened_at) {
            $this->record->update(['opened_at' => now()]);
        }
    }

    #[On('orders-changed')]
    public function refreshOrder(): void
    {
        $this->record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')->label('طباعة الفاتورة')->icon('heroicon-o-printer')
                ->url(fn () => route('orders.print', $this->record))->openUrlInNewTab(),
            Action::make('receipt')->label('عرض إثبات الدفع')->icon('heroicon-o-photo')
                ->modalHeading('معاينة إثبات الدفع')
                ->modalContent(fn () => view('admin.payment-receipt-preview', ['order' => $this->record]))
                ->modalSubmitAction(false)->modalCancelActionLabel('إغلاق')
                ->visible(fn () => auth()->user()?->canManageOrders() && $this->record->payment_receipt),
            Action::make('confirmPayment')->label('تأكيد استلام الدفع')->color('success')->requiresConfirmation()
                ->visible(fn () => auth()->user()?->canManageOrders() && $this->record->status === 'pending' && in_array($this->record->payment_status, ['pending', 'rejected', 'unpaid']))
                ->action(function () {
                    OrderWorkflow::payment($this->record, true);
                    $this->record->refresh();
                }),
            Action::make('rejectPayment')->label('رفض إثبات الدفع')->color('danger')
                ->schema([Textarea::make('note')->label('سبب الرفض')->required()->maxLength(1000)])
                ->visible(fn () => auth()->user()?->canManageOrders() && $this->record->status === 'pending' && $this->record->payment_status === 'pending')
                ->action(function (array $data) {
                    OrderWorkflow::payment($this->record, false, $data['note']);
                    $this->record->refresh();
                }),
            EditAction::make()->label('تعديل الحالة أو الملاحظة'),
        ];
    }
}
