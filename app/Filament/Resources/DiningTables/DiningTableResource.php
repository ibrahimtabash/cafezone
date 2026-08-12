<?php

namespace App\Filament\Resources\DiningTables;

use App\Filament\Resources\DiningTables\Pages\CreateDiningTable;
use App\Filament\Resources\DiningTables\Pages\EditDiningTable;
use App\Filament\Resources\DiningTables\Pages\ListDiningTables;
use App\Models\DiningTable;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DiningTableResource extends Resource
{
    protected static ?string $model = DiningTable::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;
    protected static ?string $navigationLabel = 'الطاولات ورموز QR';
    protected static ?string $modelLabel = 'طاولة';
    protected static ?string $pluralModelLabel = 'الطاولات';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('اسم الطاولة')->required()->maxLength(100),
            TextInput::make('code')->label('رمز الرابط')->required()->unique(ignoreRecord: true)
                ->default(fn () => Str::lower(Str::random(10)))->helperText('رمز فريد يظهر داخل رابط QR.'),
            TextInput::make('capacity')->label('عدد المقاعد')->numeric()->minValue(1)->default(4)->required(),
            Toggle::make('is_active')->label('فعالة')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('الطاولة')->searchable()->sortable(),
            TextColumn::make('capacity')->label('المقاعد'),
            TextColumn::make('orders_count')->label('عدد الطلبات')->counts('orders'),
            IconColumn::make('is_active')->label('فعالة')->boolean(),
            TextColumn::make('qr')->label('رمز QR')->state(fn () => 'عرض وطباعة')
                ->url(fn (DiningTable $record) => route('qr.table', $record))->openUrlInNewTab(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiningTables::route('/'),
            'create' => CreateDiningTable::route('/create'),
            'edit' => EditDiningTable::route('/{record}/edit'),
        ];
    }
}
