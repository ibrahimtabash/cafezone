<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function setRoleAttribute(string $role): void
    {
        $this->attributes['role'] = $role;
        $this->attributes['is_admin'] = $role === 'admin';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'editor', 'cashier', 'orders_viewer'], true) || $this->is_admin;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || (bool) $this->is_admin;
    }

    public function canManageOrders(): bool
    {
        return $this->isAdmin() || in_array($this->role, ['editor', 'cashier'], true);
    }

    public function canManageCatalog(): bool
    {
        return $this->isAdmin() || $this->role === 'editor';
    }

    public function canOnlyViewOrders(): bool
    {
        return $this->role === 'orders_viewer' && ! $this->isAdmin();
    }
}
