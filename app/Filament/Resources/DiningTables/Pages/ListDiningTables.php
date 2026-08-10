<?php
namespace App\Filament\Resources\DiningTables\Pages;
use App\Filament\Resources\DiningTables\DiningTableResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListDiningTables extends ListRecords
{
    protected static string $resource = DiningTableResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')->label('تنزيل الطاولات وQR Excel')
                ->icon('heroicon-o-arrow-down-tray')->color('success')
                ->url(fn (): string => route('admin.dining-tables.export')),
            Action::make('takeawayQr')->label('QR الطلبات الخارجية')->url(route('qr.takeaway'))->openUrlInNewTab(),
            CreateAction::make()->label('إضافة طاولة'),
        ];
    }
}
