<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'papel',
        'ativo',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'ativo' => 'boolean',
        ];
    }

    // Relacionamentos

    public function vinculoTime(): HasOne
    {
        return $this->hasOne(VinculoTime::class);
    }

    /**
     * Retorna o locador vinculado ao usuário
     */
    public function locador(): ?Pessoa
    {
        return $this->vinculoTime?->locador;
    }

    // Helpers

    public function isAdmin(): bool
    {
        return $this->papel === 'admin';
    }

    public function isCliente(): bool
    {
        return $this->papel === 'cliente';
    }

    public function temLocador(): bool
    {
        return $this->vinculoTime !== null;
    }

    // Scopes

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopePorPapel(Builder $query, string $papel): Builder
    {
        return $query->where('papel', $papel);
    }
}
