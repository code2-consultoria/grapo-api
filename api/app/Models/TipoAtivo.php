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
        'valor_diaria_sugerido',
    ];

    protected function casts(): array
    {
        return [
            'valor_diaria_sugerido' => 'decimal:2',
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

    // Helpers

    public function quantidadeDisponivel(): int
    {
        return $this->lotes()
            ->where('status', 'disponivel')
            ->sum('quantidade_disponivel');
    }
}
