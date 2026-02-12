<?php

namespace App\Actions\Lote;

use App\Contracts\Command;
use App\Models\Lote;

/**
 * Libera unidades de um lote (aumenta quantidade disponível).
 */
class LiberarUnidades implements Command
{
    public function __construct(
        private Lote $lote,
        private int $quantidade
    ) {}

    /**
     * Executa a liberação das unidades.
     */
    public function handle(): void
    {
        $novaQuantidade = $this->lote->quantidade_disponivel + $this->quantidade;

        // Garante que não ultrapasse a quantidade total
        $this->lote->quantidade_disponivel = min($novaQuantidade, $this->lote->quantidade_total);
        $this->lote->save();
    }
}
