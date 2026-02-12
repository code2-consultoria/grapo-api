<?php

namespace App\Actions\Alocacao;

use App\Actions\Lote\LiberarUnidades;
use App\Contracts\Command;
use App\Models\ContratoItem;
use Illuminate\Support\Facades\DB;

/**
 * Libera as alocações de um item do contrato.
 *
 * Retorna as quantidades alocadas de volta aos lotes de origem.
 */
class Liberar implements Command
{
    public function __construct(
        private ContratoItem $item
    ) {}

    /**
     * Executa a liberação das alocações do item.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $alocacoes = $this->item->alocacoes()->with('lote')->get();

            foreach ($alocacoes as $alocacao) {
                // Retorna quantidade ao lote usando Action
                (new LiberarUnidades($alocacao->lote, $alocacao->quantidade_alocada))->handle();

                // Remove registro de alocação
                $alocacao->delete();
            }
        });
    }
}
