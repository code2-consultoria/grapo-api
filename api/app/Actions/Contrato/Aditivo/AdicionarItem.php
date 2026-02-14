<?php

namespace App\Actions\Contrato\Aditivo;

use App\Contracts\Command;
use App\Exceptions\AditivoImutavelException;
use App\Models\ContratoAditivo;
use App\Models\ContratoAditivoItem;
use App\Models\TipoAtivo;

/**
 * Adiciona um item ao aditivo em rascunho.
 */
class AdicionarItem implements Command
{
    private ContratoAditivoItem $item;

    public function __construct(
        private ContratoAditivo $aditivo,
        private TipoAtivo $tipoAtivo,
        private int $quantidadeAlterada,
        private ?float $valorUnitario = null,
    ) {}

    /**
     * Executa a adição do item ao aditivo.
     *
     * @throws AditivoImutavelException
     */
    public function handle(): void
    {
        // RN02 - Aditivo em rascunho é editável
        if (! $this->aditivo->podeSerEditado()) {
            throw new AditivoImutavelException(
                $this->aditivo->id,
                $this->aditivo->status
            );
        }

        $this->item = new ContratoAditivoItem([
            'quantidade_alterada' => $this->quantidadeAlterada,
            'valor_unitario' => $this->valorUnitario,
        ]);

        $this->item->aditivo()->associate($this->aditivo);
        $this->item->tipoAtivo()->associate($this->tipoAtivo);
        $this->item->save();
    }

    /**
     * Retorna o item criado.
     */
    public function getItem(): ContratoAditivoItem
    {
        return $this->item;
    }
}
