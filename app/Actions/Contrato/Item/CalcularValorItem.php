<?php

namespace App\Actions\Contrato\Item;

use App\Contracts\Command;
use App\Models\ContratoItem;

/**
 * Calcula e atualiza o valor total de um item do contrato.
 */
class CalcularValorItem implements Command
{
    public function __construct(
        private ContratoItem $item,
        private int $diasLocacao
    ) {}

    /**
     * Executa o cálculo do valor total do item.
     * - Diaria: quantidade * valor_unitario * dias
     * - Mensal: quantidade * valor_unitario * meses (arredonda para cima)
     */
    public function handle(): void
    {
        $multiplicador = $this->diasLocacao;

        if ($this->item->periodo_aluguel === 'mensal') {
            // Arredonda para cima o numero de meses
            $multiplicador = (int) ceil($this->diasLocacao / 30);
        }

        $this->item->valor_total_item = $this->item->quantidade * $this->item->valor_unitario * $multiplicador;
        $this->item->save();
    }
}
