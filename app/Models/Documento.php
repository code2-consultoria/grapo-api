<?php

namespace App\Models;

use App\Enums\TipoDocumento;
use App\Services\Documentos\DocumentoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'documentos';

    protected $fillable = [
        'tipo',
        'numero',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoDocumento::class,
        ];
    }

    // Relacionamentos

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    // Scopes

    public function scopePorTipo(Builder $query, TipoDocumento $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    // Accessors

    /**
     * Retorna o número do documento formatado
     */
    public function getNumeroFormatadoAttribute(): string
    {
        return DocumentoFactory::formatar($this->tipo, $this->numero);
    }

    // Helpers

    /**
     * Valida se o número do documento é válido
     */
    public function validar(): bool
    {
        return DocumentoFactory::validar($this->tipo, $this->numero);
    }

    /**
     * Retorna a mensagem de erro de validação para este tipo de documento
     */
    public function mensagemErro(): string
    {
        return DocumentoFactory::mensagemErro($this->tipo);
    }

    /**
     * Limpa a formatação do número do documento
     */
    public function limparNumero(): string
    {
        return DocumentoFactory::limpar($this->tipo, $this->numero);
    }
}
