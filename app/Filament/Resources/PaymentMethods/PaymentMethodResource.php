<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Models\PaymentMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationLabel = 'طرق الدفع والحسابات';

    protected static ?string $modelLabel = 'طريقة دفع';

    protected static ?string $pluralModelLabel = 'طرق الدفع';

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageCatalog() === true;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('اسم البنك أو المحفظة')->required()->maxLength(255),
            TextInput::make('account_name')->label('اسم صاحب الحساب')->required()->maxLength(255),
            TextInput::make('account_number')->label('رقم الحساب / IBAN / المحفظة')->required()->maxLength(255),
            FileUpload::make('qr_code')->label('QR Code للدفع')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->maxSize(5120)->disk('public')->directory('payment-qr'),
            Textarea::make('instructions')->label('تعليمات الدفع')->maxLength(2000),
            Toggle::make('is_active')->label('متاح للزبائن')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('الطريقة'), TextColumn::make('account_name')->label('صاحب الحساب'),
            TextColumn::make('account_number')->label('رقم الحساب'), IconColumn::make('is_active')->label('فعال')->boolean()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePaymentMethods::route('/')];
    }
}
