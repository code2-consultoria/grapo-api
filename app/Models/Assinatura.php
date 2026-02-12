<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assinatura extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assinaturas';

    protected $fillable = [
        'data_inicio',
        'data_termino',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_termino' => 'date',
        ];
    }

    // Relacionamentos

    public function locador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'locador_id');
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    // Scopes

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('status', 'ativa')
            ->where('data_termino', '>=', now()->toDateString());
    }

    // Helpers

    public function estaAtiva(): bool
    {
        return $this->status === 'ativa' && $this->data_termino >= now()->toDateString();
    }
}
