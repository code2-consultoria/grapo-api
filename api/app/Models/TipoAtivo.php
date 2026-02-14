<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoAtivo extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tipos_ativos';

    protected $fillable = [
        'nome',
        'descricao',
        'unidade_medida',
        'valor_mensal_sugerido',
    ];

    protected $appends = [
        'valor_diaria_sugerido',
    ];

    protected function casts(): array
    {
        return [
            'valor_mensal_sugerido' => 'decimal:2',
        ];
    }

    // Relacionamentos

    public function locador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'locador_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function contratoItens(): HasMany
    {
        return $this->hasMany(ContratoItem::class);
    }

    // Accessors

    /**
     * Calcula o valor da diaria sugerida a partir do valor mensal.
     * Formula: (valor_mensal * (1 + majoracao/100)) / 30
     * Majoracao configuravel por locador (padrao 10%).
     */
    public function getValorDiariaSugeridoAttribute(): float
    {
        if (! $this->valor_mensal_sugerido) {
            return 0;
        }

        // Busca a majoracao do locador (padrao 10%)
        $majoracaoPercent = $this->locador?->majoracao_diaria ?? 10.00;
        $majoracao = 1 + ($majoracaoPercent / 100);

        return round(($this->valor_mensal_sugerido * $majoracao) / 30, 2);
    }

    // Helpers

    public function quantidadeDisponivel(): int
    {
        return $this->lotes()
            ->where('status', 'disponivel')
            ->sum('quantidade_disponivel');
    }
}
