<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public function mount(): void
    {
        if (auth()->user()?->canOnlyViewOrders() === true) {
            $this->redirect(OrderResource::getUrl(), navigate: true);
        }
    }
}
