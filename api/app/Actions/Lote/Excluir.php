<?php

namespace App\Actions\Lote;

use App\Contracts\Command;
use App\Exceptions\LoteComAlocacoesException;
use App\Models\Lote;

/**
 * Exclui um lote.
 */
class Excluir implements Command
{
    public function __construct(
        private Lote $lote
    ) {}

    /**
     * Executa a exclusão do lote.
     *
     * @throws LoteComAlocacoesException
     */
    public function handle(): void
    {
        // Verifica se há alocações ativas
        if ($this->lote->alocacoes()->exists()) {
            throw new LoteComAlocacoesException($this->lote->codigo);
        }

        $this->lote->delete();
    }
}
