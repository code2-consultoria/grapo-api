<?php

namespace App\Actions\Contrato\Aditivo;

use App\Contracts\Command;
use App\Exceptions\AditivoImutavelException;
use App\Models\ContratoAditivoItem;

/**
 * Remove um item do aditivo em rascunho.
 */
class RemoverItem implements Command
{
    public function __construct(
        private ContratoAditivoItem $item
    ) {}

    /**
     * Executa a remoção do item do aditivo.
     *
     * @throws AditivoImutavelException
     */
    public function handle(): void
    {
        $aditivo = $this->item->aditivo;

        // RN02 - Aditivo em rascunho é editável
        if (! $aditivo->podeSerEditado()) {
            throw new AditivoImutavelException(
                $aditivo->id,
                $aditivo->status
            );
        }

        $this->item->delete();
    }
}
