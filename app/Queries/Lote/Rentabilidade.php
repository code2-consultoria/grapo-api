<?php

namespace App\Queries\Lote;

use App\Contracts\Query;
use App\Enums\StatusContrato;
use App\Enums\StatusPagamento;
use App\Models\AlocacaoLote;
use App\Models\Contrato;
use App\Models\Lote;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Query para obter dados de rentabilidade de um lote.
 */
class Rentabilidade implements Query
{
    private const MESES_OCUPACAO = 24;

    public function __construct(
        private Lote $lote
    ) {}

    /**
     * Executa a query e retorna os dados de rentabilidade.
     */
    public function handle(): array
    {
        return [
            'lote' => $this->getDadosLote(),
            'resumo' => $this->resumo(),
            'pagamentos_por_mes' => $this->pagamentosPorMes(),
            'ocupacao_por_mes' => $this->ocupacaoPorMes(),
        ];
    }

    /**
     * Retorna dados basicos do lote.
     */
    private function getDadosLote(): array
    {
        return [
            'id' => $this->lote->id,
            'codigo' => $this->lote->codigo,
            'tipo_ativo' => $this->lote->tipoAtivo?->nome,
            'quantidade_total' => $this->lote->quantidade_total,
            'quantidade_disponivel' => $this->lote->quantidade_disponivel,
            'data_aquisicao' => $this->lote->data_aquisicao?->format('Y-m-d'),
        ];
    }

    /**
     * Calcula o resumo de rentabilidade.
     */
    public function resumo(): array
    {
        $custoAquisicao = $this->calcularCustoAquisicao();
        $totalRecebido = $this->calcularTotalRecebido();
        $roiPercentual = $custoAquisicao > 0
            ? round(($totalRecebido / $custoAquisicao) * 100, 2)
            : 0;

        $contratosIds = $this->getContratosDoLote();

        return [
            'custo_aquisicao' => round($custoAquisicao, 2),
            'total_recebido' => round($totalRecebido, 2),
            'roi_percentual' => $roiPercentual,
            'unidades_alocadas' => $this->lote->quantidade_total - $this->lote->quantidade_disponivel,
            'contratos_count' => $contratosIds->count(),
        ];
    }

    /**
     * Calcula custo de aquisicao (valor_total + valor_frete).
     */
    private function calcularCustoAquisicao(): float
    {
        return (float) ($this->lote->valor_total ?? 0) + (float) ($this->lote->valor_frete ?? 0);
    }

    /**
     * Calcula o total recebido proporcional ao lote.
     */
    private function calcularTotalRecebido(): float
    {
        $totalRecebido = 0.0;

        // Busca todas as alocacoes deste lote
        $alocacoes = AlocacaoLote::where('lote_id', $this->lote->id)
            ->with(['contratoItem.contrato.pagamentos'])
            ->get();

        foreach ($alocacoes as $alocacao) {
            $contratoItem = $alocacao->contratoItem;
            if (!$contratoItem) {
                continue;
            }

            $contrato = $contratoItem->contrato;
            if (!$contrato) {
                continue;
            }

            // Calcula proporcao: quantidade_alocada / quantidade_total_item
            $quantidadeTotalItem = $contratoItem->quantidade;
            if ($quantidadeTotalItem <= 0) {
                continue;
            }

            $proporcao = $alocacao->quantidade_alocada / $quantidadeTotalItem;

            // Soma pagamentos pagos do contrato * proporcao
            $pagamentosPagos = $contrato->pagamentos
                ->where('status', StatusPagamento::Pago);

            foreach ($pagamentosPagos as $pagamento) {
                $valorFinal = (float) $pagamento->valor - (float) ($pagamento->desconto_comercial ?? 0);
                $totalRecebido += $valorFinal * $proporcao;
            }
        }

        return $totalRecebido;
    }

    /**
     * Retorna IDs dos contratos que usam este lote.
     */
    private function getContratosDoLote(): Collection
    {
        return AlocacaoLote::where('lote_id', $this->lote->id)
            ->with('contratoItem')
            ->get()
            ->map(fn($alocacao) => $alocacao->contratoItem?->contrato_id)
            ->filter()
            ->unique();
    }

    /**
     * Retorna evolucao de pagamentos por mes com valor acumulado.
     */
    public function pagamentosPorMes(): array
    {
        $resultado = [];

        // Busca todas as alocacoes deste lote
        $alocacoes = AlocacaoLote::where('lote_id', $this->lote->id)
            ->with(['contratoItem.contrato.pagamentos'])
            ->get();

        // Agrupa pagamentos por mes
        $pagamentosPorMes = [];

        foreach ($alocacoes as $alocacao) {
            $contratoItem = $alocacao->contratoItem;
            if (!$contratoItem) {
                continue;
            }

            $contrato = $contratoItem->contrato;
            if (!$contrato) {
                continue;
            }

            $quantidadeTotalItem = $contratoItem->quantidade;
            if ($quantidadeTotalItem <= 0) {
                continue;
            }

            $proporcao = $alocacao->quantidade_alocada / $quantidadeTotalItem;

            $pagamentosPagos = $contrato->pagamentos
                ->where('status', StatusPagamento::Pago)
                ->whereNotNull('data_pagamento');

            foreach ($pagamentosPagos as $pagamento) {
                $mes = Carbon::parse($pagamento->data_pagamento)->format('Y-m');
                $valorFinal = (float) $pagamento->valor - (float) ($pagamento->desconto_comercial ?? 0);
                $valorProporcional = $valorFinal * $proporcao;

                if (!isset($pagamentosPorMes[$mes])) {
                    $pagamentosPorMes[$mes] = 0;
                }
                $pagamentosPorMes[$mes] += $valorProporcional;
            }
        }

        // Ordena por mes e calcula acumulado
        ksort($pagamentosPorMes);

        $acumulado = 0;
        foreach ($pagamentosPorMes as $mes => $valor) {
            $acumulado += $valor;
            $resultado[] = [
                'mes' => $mes,
                'valor' => round($valor, 2),
                'acumulado' => round($acumulado, 2),
            ];
        }

        return $resultado;
    }

    /**
     * Retorna taxa de ocupacao dos ultimos 24 meses (ordem decrescente: mais recente primeiro).
     */
    public function ocupacaoPorMes(): array
    {
        $resultado = [];
        $hoje = Carbon::now();

        // Gera os ultimos 24 meses em ordem decrescente (mais recente primeiro)
        for ($i = 0; $i < self::MESES_OCUPACAO; $i++) {
            $mes = $hoje->copy()->subMonths($i);
            $inicioMes = $mes->copy()->startOfMonth();
            $fimMes = $mes->copy()->endOfMonth();
            $diasNoMes = $inicioMes->daysInMonth;

            $percentual = $this->calcularOcupacaoMes($inicioMes, $fimMes, $diasNoMes);

            $resultado[] = [
                'mes' => $inicioMes->format('Y-m'),
                'percentual' => round($percentual, 1),
            ];
        }

        return $resultado;
    }

    /**
     * Calcula a ocupacao do lote em um mes especifico.
     */
    private function calcularOcupacaoMes(Carbon $inicioMes, Carbon $fimMes, int $diasNoMes): float
    {
        $quantidadeTotal = $this->lote->quantidade_total;
        if ($quantidadeTotal <= 0) {
            return 0;
        }

        // Busca alocacoes deste lote em contratos ativos/finalizados no periodo
        $alocacoes = AlocacaoLote::where('lote_id', $this->lote->id)
            ->with(['contratoItem.contrato'])
            ->get();

        $totalDiasUnidades = 0; // soma de (dias_alocados * unidades_alocadas)

        foreach ($alocacoes as $alocacao) {
            $contrato = $alocacao->contratoItem?->contrato;
            if (!$contrato) {
                continue;
            }

            // Apenas contratos ativos ou finalizados
            if (!in_array($contrato->status, [StatusContrato::Ativo, StatusContrato::Finalizado])) {
                continue;
            }

            // Verifica se o contrato estava ativo no periodo
            $contratoInicio = Carbon::parse($contrato->data_inicio)->copy();
            $contratoFim = Carbon::parse($contrato->data_termino)->copy();

            // Calcula intersecao do periodo do contrato com o mes
            // Usar max/min manualmente para evitar mutacao do Carbon
            $inicioEfetivo = $contratoInicio->gt($inicioMes) ? $contratoInicio->copy() : $inicioMes->copy();
            $fimEfetivo = $contratoFim->lt($fimMes) ? $contratoFim->copy() : $fimMes->copy();

            if ($inicioEfetivo->lte($fimEfetivo)) {
                $diasAlocados = $inicioEfetivo->diffInDays($fimEfetivo) + 1;
                $totalDiasUnidades += $diasAlocados * $alocacao->quantidade_alocada;
            }
        }

        // Capacidade maxima = dias_no_mes * quantidade_total
        $capacidadeMaxima = $diasNoMes * $quantidadeTotal;

        if ($capacidadeMaxima <= 0) {
            return 0;
        }

        // Limita a 100% para evitar inconsistencias
        $percentual = ($totalDiasUnidades / $capacidadeMaxima) * 100;
        return min($percentual, 100);
    }
}
