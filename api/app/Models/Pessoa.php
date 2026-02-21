<?php

namespace App\Models;

use App\Casts\StripeConnectConfigCast;
use App\Enums\TipoDocumento;
use App\Enums\TipoPessoa;
use App\ValueObjects\StripeConnectConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;

class Pessoa extends Model
{
    use Billable, HasFactory, HasUuids;

    protected $table = 'pessoas';

    protected $fillable = [
        'tipo',
        'nome',
        'email',
        'telefone',
        'endereco',
        'ativo',
        'majoracao_diaria',
        'stripe_connect_config',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoPessoa::class,
            'ativo' => 'boolean',
            'data_limite_acesso' => 'date',
            'majoracao_diaria' => 'decimal:2',
            'stripe_connect_config' => StripeConnectConfigCast::class,
        ];
    }

    /**
     * Retorna a configuração do Stripe Connect (sempre retorna um objeto, nunca null).
     */
    public function stripeConnect(): StripeConnectConfig
    {
        return $this->stripe_connect_config ?? new StripeConnectConfig;
    }

    /**
     * Atualiza a configuração do Stripe Connect.
     */
    public function updateStripeConnect(StripeConnectConfig $config): bool
    {
        return $this->update(['stripe_connect_config' => $config]);
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

    // Stripe Subscriptions (sobrescreve Billable)

    /**
     * Retorna as subscriptions Stripe do locador.
     */
    public function stripeSubscriptions(): HasMany
    {
        return $this->hasMany(StripeSubscription::class, 'pessoa_id');
    }

    /**
     * Define a foreign key usada pelo Cashier (pessoa_id ao invés de user_id).
     */
    public function getForeignKey(): string
    {
        return 'pessoa_id';
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

    /**
     * Verifica se o locador tem acesso ativo (data_limite_acesso >= hoje).
     */
    public function hasAcessoAtivo(): bool
    {
        if (! $this->isLocador()) {
            return true;
        }

        if ($this->data_limite_acesso === null) {
            return false;
        }

        return $this->data_limite_acesso->gte(now()->startOfDay());
    }

    /**
     * Define a data limite de acesso para trial.
     */
    public function definirTrial(): void
    {
        $dias = config('assinatura.trial_dias', 7);
        $this->data_limite_acesso = now()->addDays($dias);
        $this->save();
    }

    /**
     * Atualiza a data limite de acesso após pagamento.
     */
    public function atualizarAcessoPorPagamento(): void
    {
        $dias = config('assinatura.dias_apos_pagamento', 60);
        $this->data_limite_acesso = now()->addDays($dias);
        $this->save();
    }

    /**
     * Define a data limite de acesso para cancelamento.
     */
    public function definirAcessoCancelamento(): void
    {
        $dias = config('assinatura.dias_apos_cancelamento', 30);
        $dataLimite = now()->addDays($dias);

        // Só reduz se a data atual for maior
        if ($this->data_limite_acesso === null || $this->data_limite_acesso->gt($dataLimite)) {
            $this->data_limite_acesso = $dataLimite;
            $this->save();
        }
    }
}
