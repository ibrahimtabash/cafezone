<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('الطلبات', Order::count()),

            Stat::make('التصنيفات', Category::count()),

            Stat::make('الأصناف', MenuItem::count()),

            Stat::make(
                'إجمالي المبيعات',
                Order::sum('total') . ' ₪'
            ),
            Stat::make(
                'إجمالي المبيعات بدون التوصيل',
                Order::sum('subtotal') . ' ₪'
            ),
        ];
    }
}
