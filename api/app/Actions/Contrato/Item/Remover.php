<?php

namespace App\Actions\Contrato\Item;

use App\Actions\Contrato\CalcularValorTotal;
use App\Contracts\Command;
use App\Exceptions\ContratoAtivoImutavelException;
use App\Models\ContratoItem;

/**
 * Remove um item do contrato.
 */
class Remover implements Command
{
    public function __construct(
        private ContratoItem $item
    ) {}

    /**
     * Executa a remoção do item do contrato.
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

        // Remove o item
        $this->item->delete();

        // Atualiza valor total do contrato usando Action
        (new CalcularValorTotal($contrato))->handle();
    }
}
