<?php

namespace App\Actions\Lote;

use App\Contracts\Command;
use App\Exceptions\QuantidadeIndisponivelException;
use App\Models\Lote;

/**
 * Aloca unidades de um lote (diminui quantidade disponível).
 */
class AlocarUnidades implements Command
{
    public function __construct(
        private Lote $lote,
        private int $quantidade
    ) {}

    /**
     * Executa a alocação das unidades.
     *
     * @throws QuantidadeIndisponivelException
     */
    public function handle(): void
    {
        if (! $this->lote->temDisponibilidade($this->quantidade)) {
            throw new QuantidadeIndisponivelException(
                $this->lote->tipoAtivo->nome,
                $this->lote->tipo_ativo_id,
                $this->quantidade,
                $this->lote->quantidade_disponivel
            );
        }

        $this->lote->quantidade_disponivel -= $this->quantidade;
        $this->lote->save();
    }
}
