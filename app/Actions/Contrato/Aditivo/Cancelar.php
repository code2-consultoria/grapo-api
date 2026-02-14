<?php

namespace App\Actions\Contrato\Aditivo;

use App\Actions\Alocacao\Alocar;
use App\Actions\Alocacao\Liberar;
use App\Actions\Contrato\Aditivo\Stripe\ReverterSubscription;
use App\Contracts\Command;
use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use App\Exceptions\AditivoNaoPodeSerCanceladoException;
use App\Models\ContratoAditivo;
use App\Models\ContratoItem;
use Illuminate\Support\Facades\DB;

/**
 * Cancela um aditivo e reverte as alterações se estiver ativo.
 */
class Cancelar implements Command
{
    public function __construct(
        private ContratoAditivo $aditivo
    ) {}

    /**
     * Executa o cancelamento do aditivo.
     */
    public function handle(): void
    {
        if (! $this->aditivo->podeSerCancelado()) {
            throw new AditivoNaoPodeSerCanceladoException(
                $this->aditivo->id,
                $this->aditivo->status
            );
        }

        DB::transaction(function () {
            // Se o aditivo está ativo, precisa reverter
            if ($this->aditivo->estaAtivo()) {
                $this->reverterAditivo();
            }

            // Marca como cancelado
            $this->aditivo->status = StatusAditivo::Cancelado;
            $this->aditivo->save();
        });
    }

    /**
     * Reverte as alterações do aditivo ativo.
     */
    private function reverterAditivo(): void
    {
        $contrato = $this->aditivo->contrato;

        switch ($this->aditivo->tipo) {
            case TipoAditivo::Prorrogacao:
                $this->reverterProrrogacao($contrato);
                break;

            case TipoAditivo::Acrescimo:
                $this->reverterAcrescimo($contrato);
                break;

            case TipoAditivo::Reducao:
                $this->reverterReducao($contrato);
                break;

            case TipoAditivo::AlteracaoValor:
                $this->reverterAlteracaoValor($contrato);
                break;
        }

        // Reverte Stripe se aplicável
        $this->reverterStripe($contrato);
    }

    /**
     * Reverte alterações no Stripe.
     */
    private function reverterStripe($contrato): void
    {
        // Verifica se contrato tem cobrança Stripe
        if (! $contrato->temCobrancaStripe() || ! $contrato->stripe_subscription_id) {
            return;
        }

        // Reverte subscription para o preço anterior
        (new ReverterSubscription($contrato, $this->aditivo))->handle();
    }

    /**
     * Reverte prorrogação: restaura data_termino original.
     */
    private function reverterProrrogacao($contrato): void
    {
        if ($this->aditivo->data_termino_anterior) {
            $contrato->data_termino = $this->aditivo->data_termino_anterior;
            $contrato->save();

            // Recalcula valor total baseado no período original
            $this->recalcularValorTotal($contrato);
        }
    }

    /**
     * Reverte acréscimo: libera itens alocados.
     */
    private function reverterAcrescimo($contrato): void
    {
        $valorReduzir = 0;

        foreach ($this->aditivo->itens as $aditivoItem) {
            // Busca item no contrato
            $contratoItem = $contrato->itens()
                ->where('tipo_ativo_id', $aditivoItem->tipo_ativo_id)
                ->first();

            if ($contratoItem) {
                // Quantidade a liberar
                $quantidadeLiberar = min(
                    $aditivoItem->quantidade_alterada,
                    $contratoItem->quantidadeAlocada()
                );

                if ($quantidadeLiberar > 0) {
                    // Libera usando a action existente
                    (new \App\Actions\Alocacao\LiberarLIFO($contratoItem, $quantidadeLiberar))->handle();
                }

                // Atualiza quantidade do item
                $contratoItem->quantidade -= $aditivoItem->quantidade_alterada;

                if ($contratoItem->quantidade <= 0) {
                    $contratoItem->delete();
                } else {
                    $contratoItem->save();
                }

                // Calcula valor a reduzir
                $diasLocacao = $contrato->calcularDiasLocacao();
                $valorReduzir += $aditivoItem->quantidade_alterada *
                    ($aditivoItem->valor_unitario ?? 0) * $diasLocacao;
            }
        }

        // Atualiza valor total do contrato
        $contrato->valor_total = max(0, (float) $contrato->valor_total - $valorReduzir);
        $contrato->save();
    }

    /**
     * Reverte redução: realoca itens liberados.
     */
    private function reverterReducao($contrato): void
    {
        $valorAdicional = 0;

        foreach ($this->aditivo->itens as $aditivoItem) {
            $quantidadeRealocar = abs($aditivoItem->quantidade_alterada);

            // Busca ou cria item no contrato
            $contratoItem = $contrato->itens()
                ->where('tipo_ativo_id', $aditivoItem->tipo_ativo_id)
                ->first();

            if ($contratoItem) {
                // Atualiza quantidade do item existente
                $contratoItem->quantidade += $quantidadeRealocar;
                $contratoItem->save();
            } else {
                // Recria item no contrato
                $contratoItem = new ContratoItem([
                    'quantidade' => $quantidadeRealocar,
                    'valor_unitario' => $aditivoItem->valor_unitario ?? 0,
                    'periodo_aluguel' => 'diaria',
                    'valor_total_item' => 0,
                ]);
                $contratoItem->contrato()->associate($contrato);
                $contratoItem->tipoAtivo()->associate($aditivoItem->tipoAtivo);
                $contratoItem->save();
            }

            // Realoca lotes usando FIFO existente
            $itemParaAlocar = new ContratoItem([
                'quantidade' => $quantidadeRealocar,
            ]);
            $itemParaAlocar->id = $contratoItem->id;
            $itemParaAlocar->setRelation('contrato', $contrato);
            $itemParaAlocar->setRelation('tipoAtivo', $aditivoItem->tipoAtivo);
            $itemParaAlocar->tipo_ativo_id = $aditivoItem->tipo_ativo_id;

            (new Alocar($itemParaAlocar))->handle();

            // Calcula valor adicional
            $diasLocacao = $contrato->calcularDiasLocacao();
            $valorAdicional += $quantidadeRealocar *
                ($contratoItem->valor_unitario ?? 0) * $diasLocacao;
        }

        // Atualiza valor total do contrato
        $contrato->valor_total = (float) $contrato->valor_total + $valorAdicional;
        $contrato->save();
    }

    /**
     * Reverte alteração de valor: subtrai valor_ajuste.
     */
    private function reverterAlteracaoValor($contrato): void
    {
        if ($this->aditivo->valor_total_anterior !== null) {
            $contrato->valor_total = $this->aditivo->valor_total_anterior;
        } else {
            $contrato->valor_total = (float) $contrato->valor_total - (float) $this->aditivo->valor_ajuste;
        }
        $contrato->save();
    }

    /**
     * Recalcula valor total do contrato baseado nos itens.
     */
    private function recalcularValorTotal($contrato): void
    {
        $diasLocacao = $contrato->calcularDiasLocacao();
        $valorTotal = 0;

        foreach ($contrato->itens as $item) {
            $valorItem = $item->quantidade * ($item->valor_unitario ?? 0) * $diasLocacao;
            $item->valor_total_item = $valorItem;
            $item->save();
            $valorTotal += $valorItem;
        }

        $contrato->valor_total = $valorTotal;
        $contrato->save();
    }

    /**
     * Retorna o aditivo atualizado.
     */
    public function getAditivo(): ContratoAditivo
    {
        return $this->aditivo->fresh();
    }
}
