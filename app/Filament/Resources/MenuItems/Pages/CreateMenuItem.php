<?php

namespace App\Filament\Resources\MenuItems\Pages;

use App\Filament\Resources\MenuItems\MenuItemResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('تمت إضافة الصنف بنجاح ☕')
            ->body("أصبح «{$this->record->name}» متاحًا الآن ضمن قائمة المنيو.")
            ->icon('heroicon-o-sparkles')
            ->iconColor('warning')
            ->color('warning')
            ->seconds(6);
    }
}
