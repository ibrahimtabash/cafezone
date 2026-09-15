<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class StatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    #[On('orders-changed')]
    public function refreshOrders(): void {}

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('الطلبات', Order::count()),

            Stat::make('طلبات داخل الكافي اليوم', Order::where('order_type', 'dine_in')->whereDate('created_at', today())->count()),

            Stat::make('طلبات بحاجة للمتابعة', Order::whereIn('status', ['pending', 'confirmed', 'preparing'])->count()),

            Stat::make('التصنيفات', Category::count()),

            Stat::make('الأصناف', MenuItem::count()),

            Stat::make(
                'إجمالي المبيعات',
                Order::sum('total').' ₪'
            ),
            Stat::make(
                'إجمالي المبيعات بدون التوصيل',
                Order::sum('subtotal').' ₪'
            ),
        ];
    }
}
