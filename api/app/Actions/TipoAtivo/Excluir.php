<?php

namespace App\Actions\TipoAtivo;

use App\Contracts\Command;
use App\Models\TipoAtivo;

/**
 * Exclui um tipo de ativo.
 */
class Excluir implements Command
{
    public function __construct(
        private TipoAtivo $tipoAtivo
    ) {}

    /**
     * Executa a exclusão do tipo de ativo.
     */
    public function handle(): void
    {
        $this->tipoAtivo->delete();
    }
}
