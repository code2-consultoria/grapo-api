<?php

namespace App\Actions\Contrato;

use App\Actions\Alocacao\Liberar;
use App\Contracts\Command;
use App\Models\Contrato;
use Illuminate\Support\Facades\DB;

/**
 * Cancela um contrato ativo e libera os lotes alocados.
 */
class Cancelar implements Command
{
    public function __construct(
        private Contrato $contrato
    ) {}

    /**
     * Executa o cancelamento do contrato.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            // Libera alocações de cada item
            foreach ($this->contrato->itens as $item) {
                (new Liberar($item))->handle();
            }

            // Atualiza status
            $this->contrato->status = 'cancelado';
            $this->contrato->save();
        });
    }

    /**
     * Retorna o contrato atualizado.
     */
    public function getContrato(): Contrato
    {
        return $this->contrato->fresh();
    }
}
