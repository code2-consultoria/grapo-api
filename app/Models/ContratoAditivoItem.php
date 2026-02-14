<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratoAditivoItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contrato_aditivo_itens';

    protected $fillable = [
        'quantidade_alterada',
        'valor_unitario',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_alterada' => 'integer',
            'valor_unitario' => 'decimal:2',
        ];
    }

    // Relacionamentos

    public function aditivo(): BelongsTo
    {
        return $this->belongsTo(ContratoAditivo::class, 'contrato_aditivo_id');
    }

    public function tipoAtivo(): BelongsTo
    {
        return $this->belongsTo(TipoAtivo::class);
    }

    // Helpers

    public function isAcrescimo(): bool
    {
        return $this->quantidade_alterada > 0;
    }

    public function isReducao(): bool
    {
        return $this->quantidade_alterada < 0;
    }

    public function quantidadeAbsoluta(): int
    {
        return abs($this->quantidade_alterada);
    }

    public function valorTotal(): float
    {
        return $this->quantidade_alterada * ($this->valor_unitario ?? 0);
    }
}
