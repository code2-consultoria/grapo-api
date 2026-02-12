<?php

namespace App\Actions\Lote;

use App\Contracts\Command;
use App\Models\Lote;

/**
 * Atualiza um lote existente.
 */
class Atualizar implements Command
{
    public function __construct(
        private Lote $lote,
        private array $dados
    ) {}

    /**
     * Executa a atualização do lote.
     */
    public function handle(): void
    {
        $this->lote->fill($this->dados);
        $this->lote->save();
    }

    /**
     * Retorna o lote atualizado.
     */
    public function getLote(): Lote
    {
        return $this->lote;
    }
}
