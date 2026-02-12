<?php

namespace App\Actions\Contrato;

use App\Contracts\Command;
use App\Models\Contrato;

/**
 * Calcula e atualiza o valor total do contrato.
 */
class CalcularValorTotal implements Command
{
    public function __construct(
        private Contrato $contrato
    ) {}

    /**
     * Executa o cálculo do valor total do contrato.
     */
    public function handle(): void
    {
        $this->contrato->load('itens');
        $this->contrato->valor_total = $this->contrato->itens->sum('valor_total_item');
        $this->contrato->save();
    }
}
