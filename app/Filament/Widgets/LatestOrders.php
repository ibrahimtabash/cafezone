<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order as ModelsOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Livewire\Attributes\On;

class LatestOrders extends TableWidget
{
    protected static ?string $heading = 'آخر الطلبات';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    #[On('orders-changed')]
    public function refreshOrders(): void {}

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->recordClasses(fn (ModelsOrder $record) => $record->opened_at ? null : 'order-unread')
            ->recordUrl(fn (ModelsOrder $record) => OrderResource::getUrl('view', ['record' => $record]))
            ->query(
                ModelsOrder::query()->latest()
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب'),

                TextColumn::make('customer_name')
                    ->label('العميل')->placeholder('زبون الطاولة'),

                TextColumn::make('order_type')->label('النوع')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'dine_in' => 'داخل الكافي', 'takeaway' => 'خارجي', default => 'توصيل',
                }),

                TextColumn::make('diningTable.name')->label('الطاولة')->placeholder('—'),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->suffix(' ₪'),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->since(),
            ]);
    }
}
