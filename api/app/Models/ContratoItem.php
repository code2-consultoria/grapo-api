<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContratoItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contrato_itens';

    protected $fillable = [
        'quantidade',
        'valor_unitario',
        'periodo_aluguel',
        'valor_total_item',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'valor_unitario' => 'decimal:2',
            'valor_total_item' => 'decimal:2',
        ];
    }

    // Relacionamentos

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function tipoAtivo(): BelongsTo
    {
        return $this->belongsTo(TipoAtivo::class);
    }

    public function alocacoes(): HasMany
    {
        return $this->hasMany(AlocacaoLote::class);
    }

    // Helpers

    public function quantidadeAlocada(): int
    {
        return $this->alocacoes->sum('quantidade_alocada');
    }
}
