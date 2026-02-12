<?php

namespace App\Actions\Contrato\Item;

use App\Actions\Contrato\CalcularValorTotal;
use App\Contracts\Command;
use App\Exceptions\ContratoAtivoImutavelException;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\TipoAtivo;

/**
 * Adiciona um item ao contrato.
 */
class Adicionar implements Command
{
    private ContratoItem $item;

    public function __construct(
        private Contrato $contrato,
        private TipoAtivo $tipoAtivo,
        private int $quantidade,
        private float $valorUnitarioDiaria
    ) {}

    /**
     * Executa a adição do item ao contrato.
     *
     * @throws ContratoAtivoImutavelException
     */
    public function handle(): void
    {
        // Valida se contrato pode ser editado
        if (! $this->contrato->podeSerEditado()) {
            throw new ContratoAtivoImutavelException($this->contrato->codigo);
        }

        // Calcula valor total do item
        $diasLocacao = $this->contrato->calcularDiasLocacao();
        $valorTotalItem = $this->quantidade * $this->valorUnitarioDiaria * $diasLocacao;

        // Cria o item
        $this->item = new ContratoItem([
            'quantidade' => $this->quantidade,
            'valor_unitario_diaria' => $this->valorUnitarioDiaria,
            'valor_total_item' => $valorTotalItem,
        ]);

        $this->item->contrato()->associate($this->contrato);
        $this->item->tipoAtivo()->associate($this->tipoAtivo);
        $this->item->save();

        // Atualiza valor total do contrato usando Action
        (new CalcularValorTotal($this->contrato))->handle();
    }

    /**
     * Retorna o item criado.
     */
    public function getItem(): ContratoItem
    {
        return $this->item;
    }
}
