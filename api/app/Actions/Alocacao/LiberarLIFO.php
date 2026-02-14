<?php

namespace App\Actions\Alocacao;

use App\Actions\Lote\LiberarUnidades;
use App\Contracts\Command;
use App\Exceptions\ReducaoExcedeQuantidadeException;
use App\Models\ContratoItem;
use Illuminate\Support\Facades\DB;

/**
 * Libera lotes de um item do contrato usando LIFO.
 *
 * Regra: Lotes alocados mais recentemente são liberados primeiro.
 * Usado para redução de itens via aditivo.
 */
class LiberarLIFO implements Command
{
    public function __construct(
        private ContratoItem $item,
        private int $quantidadeLiberar
    ) {}

    /**
     * Executa a liberação de lotes usando LIFO.
     *
     * @throws ReducaoExcedeQuantidadeException
     */
    public function handle(): void
    {
        // Busca alocações ordenadas por LIFO (mais recentes primeiro)
        $alocacoes = $this->item->alocacoes()
            ->with('lote')
            ->orderBy('created_at', 'desc')
            ->get();

        // Verifica se há quantidade suficiente alocada
        $totalAlocado = $alocacoes->sum('quantidade_alocada');

        if ($totalAlocado < $this->quantidadeLiberar) {
            throw new ReducaoExcedeQuantidadeException(
                $this->item->tipoAtivo->nome,
                $this->quantidadeLiberar,
                $totalAlocado
            );
        }

        DB::transaction(function () use ($alocacoes) {
            $quantidadeRestante = $this->quantidadeLiberar;

            foreach ($alocacoes as $alocacao) {
                if ($quantidadeRestante <= 0) {
                    break;
                }

                // Calcula quanto liberar desta alocação
                $quantidadeLiberar = min($alocacao->quantidade_alocada, $quantidadeRestante);

                // Retorna quantidade ao lote usando Action
                (new LiberarUnidades($alocacao->lote, $quantidadeLiberar))->handle();

                // Atualiza ou remove registro de alocação
                if ($quantidadeLiberar >= $alocacao->quantidade_alocada) {
                    // Liberou toda a alocação, remove
                    $alocacao->delete();
                } else {
                    // Liberou parcialmente, atualiza
                    $alocacao->quantidade_alocada -= $quantidadeLiberar;
                    $alocacao->save();
                }

                $quantidadeRestante -= $quantidadeLiberar;
            }
        });
    }
}
