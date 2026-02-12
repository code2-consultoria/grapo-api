<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlocacaoLote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'alocacoes_lotes';

    protected $fillable = [
        'quantidade_alocada',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_alocada' => 'integer',
        ];
    }

    // Relacionamentos

    public function contratoItem(): BelongsTo
    {
        return $this->belongsTo(ContratoItem::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
