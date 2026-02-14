<?php

namespace App\Actions\Lote;

use App\Contracts\Command;
use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;

/**
 * Cria um novo lote.
 */
class Criar implements Command
{
    private Lote $lote;

    public function __construct(
        private Pessoa $locador,
        private TipoAtivo $tipoAtivo,
        private string $codigo,
        private int $quantidadeTotal,
        private ?string $fornecedor = null,
        private ?float $valorTotal = null,
        private ?float $valorFrete = null,
        private ?string $formaPagamento = null,
        private ?string $nf = null,
        private ?\DateTime $dataAquisicao = null
    ) {}

    /**
     * Executa a criação do lote.
     */
    public function handle(): void
    {
        // Calcula custo de aquisição: (valor_total + valor_frete)
        $custoAquisicao = null;
        if ($this->valorTotal !== null || $this->valorFrete !== null) {
            $custoAquisicao = ($this->valorTotal ?? 0) + ($this->valorFrete ?? 0);
        }

        $this->lote = new Lote([
            'codigo' => $this->codigo,
            'quantidade_total' => $this->quantidadeTotal,
            'quantidade_disponivel' => $this->quantidadeTotal,
            'fornecedor' => $this->fornecedor,
            'valor_total' => $this->valorTotal,
            'valor_frete' => $this->valorFrete,
            'forma_pagamento' => $this->formaPagamento,
            'nf' => $this->nf,
            'custo_aquisicao' => $custoAquisicao,
            'data_aquisicao' => $this->dataAquisicao ?? now(),
            'status' => 'disponivel',
        ]);

        $this->lote->locador()->associate($this->locador);
        $this->lote->tipoAtivo()->associate($this->tipoAtivo);
        $this->lote->save();
    }

    /**
     * Retorna o lote criado.
     */
    public function getLote(): Lote
    {
        return $this->lote;
    }
}
