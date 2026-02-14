<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lotes';

    protected $fillable = [
        'codigo',
        'quantidade_total',
        'quantidade_disponivel',
        'custo_aquisicao',
        'data_aquisicao',
        'status',
        'fornecedor',
        'valor_total',
        'valor_frete',
        'forma_pagamento',
        'nf',
    ];

    protected $appends = [
        'custo_unitario',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_total' => 'integer',
            'quantidade_disponivel' => 'integer',
            'custo_aquisicao' => 'decimal:2',
            'valor_total' => 'decimal:2',
            'valor_frete' => 'decimal:2',
            'data_aquisicao' => 'date',
        ];
    }

    // Relacionamentos

    public function locador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'locador_id');
    }

    public function tipoAtivo(): BelongsTo
    {
        return $this->belongsTo(TipoAtivo::class);
    }

    public function alocacoes(): HasMany
    {
        return $this->hasMany(AlocacaoLote::class);
    }

    // Accessors

    /**
     * Calcula o custo unitario: (valor_total + valor_frete) / quantidade_total
     */
    public function getCustoUnitarioAttribute(): ?float
    {
        if (!$this->quantidade_total || $this->quantidade_total <= 0) {
            return null;
        }

        $valorTotal = (float) ($this->valor_total ?? 0);
        $valorFrete = (float) ($this->valor_frete ?? 0);

        if ($valorTotal <= 0 && $valorFrete <= 0) {
            return null;
        }

        return round(($valorTotal + $valorFrete) / $this->quantidade_total, 2);
    }

    // Scopes

    public function scopeDisponiveis(Builder $query): Builder
    {
        return $query->where('status', 'disponivel')
            ->where('quantidade_disponivel', '>', 0);
    }

    public function scopePorTipoAtivo(Builder $query, string $tipoAtivoId): Builder
    {
        return $query->where('tipo_ativo_id', $tipoAtivoId);
    }

    public function scopeOrdenadoPorAquisicao(Builder $query): Builder
    {
        return $query->orderBy('data_aquisicao', 'asc')
            ->orderBy('created_at', 'asc');
    }

    // Helpers

    public function temDisponibilidade(int $quantidade): bool
    {
        return $this->quantidade_disponivel >= $quantidade;
    }
}
