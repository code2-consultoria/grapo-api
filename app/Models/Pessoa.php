<?php

namespace App\Models;

use App\Enums\TipoDocumento;
use App\Enums\TipoPessoa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pessoa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pessoas';

    protected $fillable = [
        'tipo',
        'nome',
        'email',
        'telefone',
        'endereco',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoPessoa::class,
            'ativo' => 'boolean',
        ];
    }

    // Relacionamentos

    /**
     * Locador ao qual esta pessoa pertence (para locatários, responsáveis)
     */
    public function locador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'locador_id');
    }

    /**
     * Locatários vinculados a este locador
     */
    public function locatarios(): HasMany
    {
        return $this->hasMany(Pessoa::class, 'locador_id')->locatarios();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function vinculoTime(): HasOne
    {
        return $this->hasOne(VinculoTime::class, 'locador_id');
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class, 'locador_id');
    }

    public function assinatura(): HasOne
    {
        return $this->hasOne(Assinatura::class, 'locador_id')->latestOfMany();
    }

    public function tiposAtivos(): HasMany
    {
        return $this->hasMany(TipoAtivo::class, 'locador_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'locador_id');
    }

    public function contratosComoLocador(): HasMany
    {
        return $this->hasMany(Contrato::class, 'locador_id');
    }

    public function contratosComoLocatario(): HasMany
    {
        return $this->hasMany(Contrato::class, 'locatario_id');
    }

    // Scopes

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeLocadores(Builder $query): Builder
    {
        return $query->where('tipo', TipoPessoa::Locador);
    }

    public function scopeLocatarios(Builder $query): Builder
    {
        return $query->where('tipo', TipoPessoa::Locatario);
    }

    public function scopePorTipo(Builder $query, TipoPessoa $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorLocador(Builder $query, Pessoa $locador): Builder
    {
        return $query->where('locador_id', $locador->id);
    }

    // Accessors

    /**
     * Retorna o tipo de pessoa (PF ou PJ) baseado nos documentos cadastrados
     * PJ: CNPJ, Inscrição Municipal, Inscrição Estadual
     * PF: CPF, RG, CNH, Passaporte, CadÚnico
     */
    public function getTipoPessoaAttribute(): ?string
    {
        $documentos = $this->documentos;

        if ($documentos->isEmpty()) {
            return null;
        }

        $temDocumentoPJ = $documentos->contains(function ($documento) {
            $tipoDocumento = $documento->tipo instanceof TipoDocumento
                ? $documento->tipo
                : TipoDocumento::tryFrom($documento->tipo);

            return $tipoDocumento?->isPessoaJuridica() ?? false;
        });

        if ($temDocumentoPJ) {
            return 'PJ';
        }

        $temDocumentoPF = $documentos->contains(function ($documento) {
            $tipoDocumento = $documento->tipo instanceof TipoDocumento
                ? $documento->tipo
                : TipoDocumento::tryFrom($documento->tipo);

            return $tipoDocumento?->isPessoaFisica() ?? false;
        });

        return $temDocumentoPF ? 'PF' : null;
    }

    // Helpers

    public function isLocador(): bool
    {
        return $this->tipo === TipoPessoa::Locador;
    }

    public function isLocatario(): bool
    {
        return $this->tipo === TipoPessoa::Locatario;
    }

    public function isPessoaJuridica(): bool
    {
        return $this->tipo_pessoa === 'PJ';
    }

    public function isPessoaFisica(): bool
    {
        return $this->tipo_pessoa === 'PF';
    }
}
