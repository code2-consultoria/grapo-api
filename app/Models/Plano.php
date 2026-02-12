<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'duracao_meses',
        'valor',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'duracao_meses' => 'integer',
            'valor' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    // Relacionamentos

    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class);
    }

    // Scopes

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
