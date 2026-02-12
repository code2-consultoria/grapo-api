<?php

namespace App\Actions\Contrato\Item;

use App\Actions\Contrato\CalcularValorTotal;
use App\Contracts\Command;
use App\Exceptions\ContratoAtivoImutavelException;
use App\Models\ContratoItem;

/**
 * Atualiza um item do contrato.
 */
class Atualizar implements Command
{
    private ContratoItem $item;

    public function __construct(
        ContratoItem $item,
        private ?int $quantidade = null,
        private ?float $valorUnitarioDiaria = null
    ) {
        $this->item = $item;
    }

    /**
     * Executa a atualização do item do contrato.
     *
     * @throws ContratoAtivoImutavelException
     */
    public function handle(): void
    {
        $contrato = $this->item->contrato;

        // Valida se contrato pode ser editado
        if (! $contrato->podeSerEditado()) {
            throw new ContratoAtivoImutavelException($contrato->codigo);
        }

        // Atualiza campos fornecidos
        if ($this->quantidade !== null) {
            $this->item->quantidade = $this->quantidade;
        }

        if ($this->valorUnitarioDiaria !== null) {
            $this->item->valor_unitario_diaria = $this->valorUnitarioDiaria;
        }

        // Recalcula valor total do item
        $diasLocacao = $contrato->calcularDiasLocacao();
        $this->item->valor_total_item = $this->item->quantidade * $this->item->valor_unitario_diaria * $diasLocacao;

        $this->item->save();

        // Atualiza valor total do contrato usando Action
        (new CalcularValorTotal($contrato))->handle();
    }

    /**
     * Retorna o item atualizado.
     */
    public function getItem(): ContratoItem
    {
        return $this->item;
    }
}
