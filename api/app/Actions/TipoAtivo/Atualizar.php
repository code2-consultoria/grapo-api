<?php

namespace App\Actions\TipoAtivo;

use App\Contracts\Command;
use App\Models\TipoAtivo;

/**
 * Atualiza um tipo de ativo existente.
 */
class Atualizar implements Command
{
    public function __construct(
        private TipoAtivo $tipoAtivo,
        private array $dados
    ) {}

    /**
     * Executa a atualização do tipo de ativo.
     */
    public function handle(): void
    {
        $this->tipoAtivo->fill($this->dados);
        $this->tipoAtivo->save();
    }

    /**
     * Retorna o tipo de ativo atualizado.
     */
    public function getTipoAtivo(): TipoAtivo
    {
        return $this->tipoAtivo;
    }
}
