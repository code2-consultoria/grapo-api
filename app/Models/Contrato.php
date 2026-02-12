<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrato extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'contratos';

    protected $fillable = [
        'codigo',
        'data_inicio',
        'data_termino',
        'valor_total',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_termino' => 'date',
            'valor_total' => 'decimal:2',
        ];
    }

    // Relacionamentos

    public function locador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'locador_id');
    }

    public function locatario(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'locatario_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ContratoItem::class);
    }

    // Scopes

    public function scopePorStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('status', 'ativo');
    }

    public function scopeRascunhos(Builder $query): Builder
    {
        return $query->where('status', 'rascunho');
    }

    // Helpers

    public function estaAtivo(): bool
    {
        return $this->status === 'ativo';
    }

    public function estaEmRascunho(): bool
    {
        return $this->status === 'rascunho';
    }

    public function podeSerEditado(): bool
    {
        return $this->estaEmRascunho();
    }

    public function calcularDiasLocacao(): int
    {
        return $this->data_inicio->diffInDays($this->data_termino) + 1;
    }
}
