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
        private float $valorUnitarioDiaria,
        private ?float $custoAquisicao = null,
        private ?\DateTime $dataAquisicao = null
    ) {}

    /**
     * Executa a criação do lote.
     */
    public function handle(): void
    {
        $this->lote = new Lote([
            'codigo' => $this->codigo,
            'quantidade_total' => $this->quantidadeTotal,
            'quantidade_disponivel' => $this->quantidadeTotal,
            'valor_unitario_diaria' => $this->valorUnitarioDiaria,
            'custo_aquisicao' => $this->custoAquisicao,
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
