<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'الحسابات والصلاحيات';
    protected static ?string $modelLabel = 'حساب';
    protected static ?string $pluralModelLabel = 'الحسابات';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('الاسم')->required()->maxLength(100),
            TextInput::make('email')->label('البريد الإلكتروني')->email()->required()->unique(ignoreRecord: true),
            Select::make('role')->label('الصلاحية')->options([
                'admin' => 'مدير — جميع الصلاحيات',
                'orders_viewer' => 'موظف طلبات — مشاهدة وإضافة طلب يدوي',
            ])->default('orders_viewer')->required()
                ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) === true)
                ->helperText(fn (?User $record): ?string => $record?->is(auth()->user()) === true
                    ? 'لا يمكنك تغيير صلاحية حسابك الحالي.'
                    : null),
            TextInput::make('password')->label('كلمة المرور')->password()->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn ($state): bool => filled($state))
                ->minLength(8)->confirmed(),
            TextInput::make('password_confirmation')->label('تأكيد كلمة المرور')->password()->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('الاسم')->searchable(),
            TextColumn::make('email')->label('البريد')->searchable(),
            TextColumn::make('role')->label('الصلاحية')->badge()->formatStateUsing(fn (?string $state) => $state === 'admin' ? 'مدير' : 'موظف طلبات'),
            TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime('Y-m-d'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->visible(fn (User $record) => $record->isNot(auth()->user())),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
