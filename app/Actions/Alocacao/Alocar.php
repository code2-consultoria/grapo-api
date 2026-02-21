<?php

namespace App\Actions\Alocacao;

use App\Actions\Lote\AlocarUnidades;
use App\Contracts\Command;
use App\Exceptions\QuantidadeIndisponivelException;
use App\Models\AlocacaoLote;
use App\Models\ContratoItem;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;

/**
 * Aloca lotes para um item do contrato usando FIFO.
 *
 * Regra: Lotes mais antigos (por data de aquisição) são alocados primeiro.
 */
class Alocar implements Command
{
    public function __construct(
        private ContratoItem $item
    ) {}

    /**
     * Executa a alocação de lotes para o item do contrato.
     *
     * @throws QuantidadeIndisponivelException
     */
    public function handle(): void
    {
        $quantidadeNecessaria = $this->item->quantidade;
        $tipoAtivoId = $this->item->tipo_ativo_id;
        $locadorId = $this->item->contrato->locador_id;

        // Busca lotes disponíveis ordenados por FIFO
        $lotesDisponiveis = Lote::where('locador_id', $locadorId)
            ->where('tipo_ativo_id', $tipoAtivoId)
            ->disponiveis()
            ->ordenadoPorAquisicao()
            ->get();

        // Verifica disponibilidade total
        $totalDisponivel = $lotesDisponiveis->sum('quantidade_disponivel');

        if ($totalDisponivel < $quantidadeNecessaria) {
            throw new QuantidadeIndisponivelException(
                $this->item->tipoAtivo->nome,
                $this->item->tipo_ativo_id,
                $quantidadeNecessaria,
                $totalDisponivel
            );
        }

        // Executa alocação dentro de transação
        DB::transaction(function () use ($lotesDisponiveis, $quantidadeNecessaria) {
            $quantidadeRestante = $quantidadeNecessaria;

            foreach ($lotesDisponiveis as $lote) {
                if ($quantidadeRestante <= 0) {
                    break;
                }

                // Calcula quanto alocar deste lote
                $quantidadeAlocar = min($lote->quantidade_disponivel, $quantidadeRestante);

                // Cria registro de alocação
                $alocacao = new AlocacaoLote([
                    'quantidade_alocada' => $quantidadeAlocar,
                ]);
                $alocacao->contratoItem()->associate($this->item);
                $alocacao->lote()->associate($lote);
                $alocacao->save();

                // Atualiza disponibilidade do lote usando Action
                (new AlocarUnidades($lote, $quantidadeAlocar))->handle();

                $quantidadeRestante -= $quantidadeAlocar;
            }
        });
    }
}
