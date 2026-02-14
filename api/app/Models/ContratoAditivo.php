<?php

namespace App\Models;

use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContratoAditivo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'contrato_aditivos';

    protected $fillable = [
        'tipo',
        'descricao',
        'data_vigencia',
        'valor_ajuste',
        'nova_data_termino',
        'conceder_reembolso',
        'status',
        'stripe_price_anterior_id',
        'stripe_invoice_item_id',
        'data_termino_anterior',
        'valor_total_anterior',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoAditivo::class,
            'status' => StatusAditivo::class,
            'data_vigencia' => 'date',
            'nova_data_termino' => 'date',
            'data_termino_anterior' => 'date',
            'valor_ajuste' => 'decimal:2',
            'valor_total_anterior' => 'decimal:2',
            'conceder_reembolso' => 'boolean',
        ];
    }

    // Relacionamentos

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ContratoAditivoItem::class);
    }

    // Helpers

    public function estaEmRascunho(): bool
    {
        return $this->status === StatusAditivo::Rascunho;
    }

    public function estaAtivo(): bool
    {
        return $this->status === StatusAditivo::Ativo;
    }

    public function estaCancelado(): bool
    {
        return $this->status === StatusAditivo::Cancelado;
    }

    public function podeSerEditado(): bool
    {
        return $this->status->podeSerEditado();
    }

    public function podeSerAtivado(): bool
    {
        return $this->status->podeSerAtivado();
    }

    public function podeSerCancelado(): bool
    {
        return $this->status->podeSerCancelado();
    }

    public function isProrrogacao(): bool
    {
        return $this->tipo === TipoAditivo::Prorrogacao;
    }

    public function isAcrescimo(): bool
    {
        return $this->tipo === TipoAditivo::Acrescimo;
    }

    public function isReducao(): bool
    {
        return $this->tipo === TipoAditivo::Reducao;
    }

    public function isAlteracaoValor(): bool
    {
        return $this->tipo === TipoAditivo::AlteracaoValor;
    }

    public function alteraItens(): bool
    {
        return $this->tipo->alteraItens();
    }

    /**
     * Calcula o valor total dos itens do aditivo.
     */
    public function calcularValorItens(): float
    {
        return $this->itens->sum(function ($item) {
            return $item->quantidade_alterada * ($item->valor_unitario ?? 0);
        });
    }
}
