<?php

namespace App\Actions\Contrato\Aditivo;

use App\Actions\Alocacao\Alocar;
use App\Actions\Alocacao\LiberarLIFO;
use App\Actions\Contrato\Aditivo\Stripe\AtualizarSubscription;
use App\Actions\Contrato\Aditivo\Stripe\CriarCobrancaProporcional;
use App\Contracts\Command;
use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use App\Exceptions\AditivoNaoPodeSerAtivadoException;
use App\Exceptions\ProrrogacaoInvalidaException;
use App\Exceptions\QuantidadeIndisponivelException;
use App\Exceptions\ReducaoExcedeQuantidadeException;
use App\Models\ContratoAditivo;
use App\Models\ContratoItem;
use Illuminate\Support\Facades\DB;

/**
 * Ativa um aditivo e aplica as alterações ao contrato.
 */
class Ativar implements Command
{
    private float $valorMensalAlteracao = 0;

    public function __construct(
        private ContratoAditivo $aditivo
    ) {}

    /**
     * Executa a ativação do aditivo.
     *
     * @throws AditivoNaoPodeSerAtivadoException
     * @throws ProrrogacaoInvalidaException
     * @throws QuantidadeIndisponivelException
     * @throws ReducaoExcedeQuantidadeException
     */
    public function handle(): void
    {
        if (! $this->aditivo->podeSerAtivado()) {
            throw new AditivoNaoPodeSerAtivadoException(
                $this->aditivo->id,
                $this->aditivo->status
            );
        }

        DB::transaction(function () {
            $contrato = $this->aditivo->contrato;

            switch ($this->aditivo->tipo) {
                case TipoAditivo::Prorrogacao:
                    $this->aplicarProrrogacao($contrato);
                    break;

                case TipoAditivo::Acrescimo:
                    $this->aplicarAcrescimo($contrato);
                    break;

                case TipoAditivo::Reducao:
                    $this->aplicarReducao($contrato);
                    break;

                case TipoAditivo::AlteracaoValor:
                    $this->aplicarAlteracaoValor($contrato);
                    break;
            }

            // Marca aditivo como ativo
            $this->aditivo->status = StatusAditivo::Ativo;
            $this->aditivo->save();

            // Integração Stripe (se contrato tem cobrança Stripe)
            $this->processarStripe($contrato);
        });
    }

    /**
     * Processa integração com Stripe se aplicável.
     */
    private function processarStripe($contrato): void
    {
        // Verifica se contrato tem cobrança Stripe
        if (! $contrato->temCobrancaStripe() || ! $contrato->stripe_subscription_id) {
            return;
        }

        // Prorrogação não afeta Stripe diretamente
        if ($this->aditivo->isProrrogacao()) {
            return;
        }

        // Calcula novo valor mensal do contrato
        $novoValorMensal = $contrato->itens->sum(function ($item) {
            return $item->quantidade * ($item->valor_unitario ?? 0) * 30; // Valor mensal
        });

        // Se for alteração de valor, adiciona o ajuste proporcional
        if ($this->aditivo->isAlteracaoValor()) {
            // Ajuste mensal = ajuste total / meses do contrato
            $diasContrato = $contrato->calcularDiasLocacao();
            $mesesContrato = max(1, $diasContrato / 30);
            $ajusteMensal = (float) $this->aditivo->valor_ajuste / $mesesContrato;
            $novoValorMensal += $ajusteMensal;
        }

        // Acréscimo: cria cobrança proporcional e atualiza subscription
        if ($this->aditivo->isAcrescimo() && $this->valorMensalAlteracao > 0) {
            (new CriarCobrancaProporcional(
                $contrato,
                $this->aditivo,
                $this->valorMensalAlteracao,
                false // Não é crédito
            ))->handle();
        }

        // Redução: cria crédito se solicitado e atualiza subscription
        if ($this->aditivo->isReducao()) {
            if ($this->aditivo->conceder_reembolso && $this->valorMensalAlteracao > 0) {
                (new CriarCobrancaProporcional(
                    $contrato,
                    $this->aditivo,
                    $this->valorMensalAlteracao,
                    true // É crédito
                ))->handle();
            }
        }

        // Atualiza subscription com novo preço
        (new AtualizarSubscription(
            $contrato,
            $this->aditivo,
            $novoValorMensal / 30 // Converte de mensal para diário para valor_unitario base
        ))->handle();
    }

    /**
     * Aplica prorrogação ao contrato.
     *
     * @throws ProrrogacaoInvalidaException
     */
    private function aplicarProrrogacao($contrato): void
    {
        // RN05 - Prorrogação não pode reduzir prazo
        if ($this->aditivo->nova_data_termino <= $contrato->data_termino) {
            throw new ProrrogacaoInvalidaException(
                $contrato->data_termino->format('Y-m-d'),
                $this->aditivo->nova_data_termino->format('Y-m-d')
            );
        }

        // Guarda data anterior para reversão
        $this->aditivo->data_termino_anterior = $contrato->data_termino;
        $this->aditivo->save();

        // Atualiza data de término
        $contrato->data_termino = $this->aditivo->nova_data_termino;
        $contrato->save();

        // Recalcula valor total se período mudou
        $this->recalcularValorTotal($contrato);
    }

    /**
     * Aplica acréscimo de itens ao contrato.
     *
     * @throws QuantidadeIndisponivelException
     */
    private function aplicarAcrescimo($contrato): void
    {
        $valorAdicional = 0;

        foreach ($this->aditivo->itens as $aditivoItem) {
            // Busca ou cria item no contrato
            $contratoItem = $contrato->itens()
                ->where('tipo_ativo_id', $aditivoItem->tipo_ativo_id)
                ->first();

            if ($contratoItem) {
                // Atualiza item existente
                $contratoItem->quantidade += $aditivoItem->quantidade_alterada;
                $contratoItem->save();
            } else {
                // Cria novo item no contrato
                $contratoItem = new ContratoItem([
                    'quantidade' => $aditivoItem->quantidade_alterada,
                    'valor_unitario' => $aditivoItem->valor_unitario ?? 0,
                    'periodo_aluguel' => 'diaria',
                    'valor_total_item' => 0,
                ]);
                $contratoItem->contrato()->associate($contrato);
                $contratoItem->tipoAtivo()->associate($aditivoItem->tipoAtivo);
                $contratoItem->save();
            }

            // Aloca lotes adicionais usando FIFO existente
            // Cria item temporário para alocação
            $itemParaAlocar = new ContratoItem([
                'quantidade' => $aditivoItem->quantidade_alterada,
            ]);
            $itemParaAlocar->id = $contratoItem->id;
            $itemParaAlocar->setRelation('contrato', $contrato);
            $itemParaAlocar->setRelation('tipoAtivo', $aditivoItem->tipoAtivo);
            $itemParaAlocar->tipo_ativo_id = $aditivoItem->tipo_ativo_id;

            (new Alocar($itemParaAlocar))->handle();

            // Calcula valor adicional
            $diasLocacao = $contrato->calcularDiasLocacao();
            $valorAdicional += $aditivoItem->quantidade_alterada *
                ($aditivoItem->valor_unitario ?? 0) * $diasLocacao;

            // Calcula valor mensal para Stripe
            $this->valorMensalAlteracao += $aditivoItem->quantidade_alterada *
                ($aditivoItem->valor_unitario ?? 0) * 30;
        }

        // Atualiza valor total do contrato
        $contrato->valor_total = (float) $contrato->valor_total + $valorAdicional;
        $contrato->save();
    }

    /**
     * Aplica redução de itens ao contrato.
     *
     * @throws ReducaoExcedeQuantidadeException
     */
    private function aplicarReducao($contrato): void
    {
        $valorReduzir = 0;

        foreach ($this->aditivo->itens as $aditivoItem) {
            $quantidadeReduzir = abs($aditivoItem->quantidade_alterada);

            // Busca item no contrato
            $contratoItem = $contrato->itens()
                ->where('tipo_ativo_id', $aditivoItem->tipo_ativo_id)
                ->first();

            if (! $contratoItem) {
                throw new ReducaoExcedeQuantidadeException(
                    $aditivoItem->tipoAtivo->nome,
                    $quantidadeReduzir,
                    0
                );
            }

            // RN03 - Valida quantidade alocada
            $quantidadeAlocada = $contratoItem->quantidadeAlocada();

            if ($quantidadeReduzir > $quantidadeAlocada) {
                throw new ReducaoExcedeQuantidadeException(
                    $aditivoItem->tipoAtivo->nome,
                    $quantidadeReduzir,
                    $quantidadeAlocada
                );
            }

            // RN04 - Libera usando LIFO
            (new LiberarLIFO($contratoItem, $quantidadeReduzir))->handle();

            // Atualiza quantidade do item
            $contratoItem->quantidade -= $quantidadeReduzir;
            $contratoItem->save();

            // Calcula valor a reduzir
            $diasLocacao = $contrato->calcularDiasLocacao();
            $valorReduzir += $quantidadeReduzir *
                ($contratoItem->valor_unitario ?? 0) * $diasLocacao;

            // Calcula valor mensal para Stripe (valor absoluto)
            $this->valorMensalAlteracao += $quantidadeReduzir *
                ($contratoItem->valor_unitario ?? 0) * 30;
        }

        // Atualiza valor total do contrato
        $contrato->valor_total = max(0, (float) $contrato->valor_total - $valorReduzir);
        $contrato->save();
    }

    /**
     * Aplica alteração de valor ao contrato.
     */
    private function aplicarAlteracaoValor($contrato): void
    {
        // Guarda valor anterior para reversão
        $this->aditivo->valor_total_anterior = $contrato->valor_total;
        $this->aditivo->save();

        // Aplica ajuste
        $contrato->valor_total = (float) $contrato->valor_total + (float) $this->aditivo->valor_ajuste;
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
