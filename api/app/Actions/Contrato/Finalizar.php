<?php

namespace App\Actions\Contrato;

use App\Actions\Alocacao\Liberar;
use App\Contracts\Command;
use App\Models\Contrato;
use Illuminate\Support\Facades\DB;

/**
 * Finaliza um contrato ativo e libera os lotes alocados.
 */
class Finalizar implements Command
{
    public function __construct(
        private Contrato $contrato
    ) {}

    /**
     * Executa a finalização do contrato.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            // Libera alocações de cada item
            foreach ($this->contrato->itens as $item) {
                (new Liberar($item))->handle();
            }

            // Atualiza status
            $this->contrato->status = 'finalizado';
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
