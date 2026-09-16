<?php

namespace App\Filament\Pages;

use App\Filament\Resources\MenuItems\MenuItemResource;
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
        if (auth()->user()?->isAdmin() === true) {
            return;
        }

        if (auth()->user()?->canManageCatalog() === true) {
            $this->redirect(MenuItemResource::getUrl(), navigate: true);

            return;
        }

        $this->redirect(OrderResource::getUrl(), navigate: true);
    }
}
