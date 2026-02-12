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
     */
    public function handle(): void
    {
        $this->item->valor_total_item = $this->item->quantidade * $this->item->valor_unitario_diaria * $this->diasLocacao;
        $this->item->save();
    }
}
