<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    #[On('orders-changed')]
    public function refreshOrders(): void {}

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة طلب يدوي'),
        ];
    }
}
