<?php

namespace App\Queries\Relatorio;

use App\Contracts\Query;
use App\Enums\StatusPagamento;
use App\Models\Contrato;
use App\Models\Pagamento;
use App\Models\Pessoa;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Query para obter dados do relatorio financeiro.
 */
class Financeiro implements Query
{
    private Carbon $dataInicio;
    private Carbon $dataFim;

    public function __construct(
        private Pessoa $locador,
        ?string $dataInicio = null,
        ?string $dataFim = null
    ) {
        // Periodo padrao: ultimos 12 meses
        $this->dataFim = $dataFim ? Carbon::parse($dataFim)->endOfDay() : Carbon::now()->endOfDay();
        $this->dataInicio = $dataInicio ? Carbon::parse($dataInicio)->startOfDay() : Carbon::now()->subMonths(12)->startOfDay();
    }

    /**
     * Executa a query e retorna os dados do relatorio.
     */
    public function handle(): array
    {
        return [
            'periodo' => [
                'inicio' => $this->dataInicio->format('Y-m-d'),
                'fim' => $this->dataFim->format('Y-m-d'),
            ],
            'resumo' => $this->getResumo(),
            'faturamento_mensal' => $this->getFaturamentoMensal(),
            'faturamento_por_ativo' => $this->getFaturamentoPorAtivo(),
            'inadimplencia' => $this->getInadimplencia(),
            'analitico_por_locatario' => $this->getAnaliticoPorLocatario(),
        ];
    }

    /**
     * Retorna resumo geral do periodo.
     */
    private function getResumo(): array
    {
        $pagamentos = $this->getPagamentosNoPeriodo();

        $totalFaturado = $pagamentos->sum(fn($p) => $this->valorFinal($p));
        $totalRecebido = $pagamentos
            ->where('status', StatusPagamento::Pago)
            ->sum(fn($p) => $this->valorFinal($p));

        $totalPendente = $pagamentos
            ->where('status', StatusPagamento::Pendente)
            ->sum(fn($p) => $this->valorFinal($p));

        $totalAtrasado = $this->getValorInadimplente($pagamentos);

        $taxaInadimplencia = $totalFaturado > 0
            ? round(($totalAtrasado / $totalFaturado) * 100, 2)
            : 0;

        return [
            'total_faturado' => round($totalFaturado, 2),
            'total_recebido' => round($totalRecebido, 2),
            'total_pendente' => round($totalPendente, 2),
            'total_atrasado' => round($totalAtrasado, 2),
            'taxa_inadimplencia' => $taxaInadimplencia,
        ];
    }

    /**
     * Retorna faturamento agrupado por mes.
     */
    private function getFaturamentoMensal(): array
    {
        $pagamentos = $this->getPagamentosNoPeriodo()
            ->where('status', StatusPagamento::Pago)
            ->whereNotNull('data_pagamento');

        $agrupado = $pagamentos->groupBy(fn($p) => Carbon::parse($p->data_pagamento)->format('Y-m'));

        $resultado = [];
        foreach ($agrupado as $mes => $pagamentosMes) {
            $resultado[] = [
                'mes' => $mes,
                'valor' => round($pagamentosMes->sum(fn($p) => $this->valorFinal($p)), 2),
                'quantidade' => $pagamentosMes->count(),
            ];
        }

        // Ordena por mes
        usort($resultado, fn($a, $b) => strcmp($a['mes'], $b['mes']));

        return $resultado;
    }

    /**
     * Retorna faturamento agrupado por tipo de ativo.
     */
    private function getFaturamentoPorAtivo(): array
    {
        $pagamentos = $this->getPagamentosNoPeriodo()
            ->where('status', StatusPagamento::Pago);

        $resultado = [];

        foreach ($pagamentos as $pagamento) {
            $contrato = $pagamento->contrato;
            if (!$contrato) {
                continue;
            }

            // Carrega itens com tipo de ativo
            $itens = $contrato->itens()->with('tipoAtivo')->get();

            if ($itens->isEmpty()) {
                // Se nao tem itens, agrupa como "Sem ativo"
                $this->addToAtivoResult($resultado, 'Sem ativo', null, $this->valorFinal($pagamento));
                continue;
            }

            // Distribui o valor proporcionalmente pelos itens
            $valorTotalItens = $itens->sum('valor_total_item');
            if ($valorTotalItens <= 0) {
                continue;
            }

            foreach ($itens as $item) {
                $proporcao = $item->valor_total_item / $valorTotalItens;
                $valorProporcional = $this->valorFinal($pagamento) * $proporcao;

                $nomeAtivo = $item->tipoAtivo?->nome ?? 'Sem ativo';
                $ativoId = $item->tipo_ativo_id;

                $this->addToAtivoResult($resultado, $nomeAtivo, $ativoId, $valorProporcional);
            }
        }

        // Converte para array e ordena por valor
        $resultadoArray = array_values($resultado);
        usort($resultadoArray, fn($a, $b) => $b['valor'] <=> $a['valor']);

        return $resultadoArray;
    }

    /**
     * Adiciona valor ao resultado de ativo.
     */
    private function addToAtivoResult(array &$resultado, string $nome, ?string $id, float $valor): void
    {
        $key = $id ?? 'sem_ativo';

        if (!isset($resultado[$key])) {
            $resultado[$key] = [
                'tipo_ativo_id' => $id,
                'tipo_ativo' => $nome,
                'valor' => 0,
                'quantidade' => 0,
            ];
        }

        $resultado[$key]['valor'] = round($resultado[$key]['valor'] + $valor, 2);
        $resultado[$key]['quantidade']++;
    }

    /**
     * Retorna dados de inadimplencia.
     */
    private function getInadimplencia(): array
    {
        $pagamentos = $this->getPagamentosInadimplentes();

        $listaPagamentos = $pagamentos->map(function ($pagamento) {
            return [
                'id' => $pagamento->id,
                'contrato_codigo' => $pagamento->contrato?->codigo,
                'locatario' => $pagamento->contrato?->locatario?->nome,
                'valor' => round($this->valorFinal($pagamento), 2),
                'data_vencimento' => $pagamento->data_vencimento?->format('Y-m-d'),
                'dias_atraso' => $pagamento->data_vencimento
                    ? (int) abs(Carbon::now()->diffInDays($pagamento->data_vencimento))
                    : 0,
            ];
        })->sortByDesc('dias_atraso')->values()->toArray();

        return [
            'quantidade' => $pagamentos->count(),
            'valor_total' => round($pagamentos->sum(fn($p) => $this->valorFinal($p)), 2),
            'pagamentos' => $listaPagamentos,
        ];
    }

    /**
     * Retorna analitico de pagamentos por locatario.
     */
    private function getAnaliticoPorLocatario(): array
    {
        $pagamentos = $this->getPagamentosNoPeriodo()
            ->where('status', StatusPagamento::Pago);

        $agrupado = $pagamentos->groupBy(fn($p) => $p->contrato?->locatario_id);

        $resultado = [];
        foreach ($agrupado as $locatarioId => $pagamentosLocatario) {
            if (!$locatarioId) {
                continue;
            }

            $locatario = $pagamentosLocatario->first()->contrato?->locatario;
            if (!$locatario) {
                continue;
            }

            $totalPago = $pagamentosLocatario->sum(fn($p) => $this->valorFinal($p));
            $qtdPagamentos = $pagamentosLocatario->count();

            // Calcula inadimplencia do locatario
            $pagamentosAtrasados = $this->getPagamentosInadimplentes()
                ->filter(fn($p) => $p->contrato?->locatario_id === $locatarioId);

            $totalAtrasado = $pagamentosAtrasados->sum(fn($p) => $this->valorFinal($p));

            $resultado[] = [
                'locatario_id' => $locatarioId,
                'locatario' => $locatario->nome,
                'total_pago' => round($totalPago, 2),
                'qtd_pagamentos' => $qtdPagamentos,
                'total_atrasado' => round($totalAtrasado, 2),
                'qtd_atrasados' => $pagamentosAtrasados->count(),
            ];
        }

        // Ordena por total pago
        usort($resultado, fn($a, $b) => $b['total_pago'] <=> $a['total_pago']);

        return $resultado;
    }

    /**
     * Retorna pagamentos do periodo.
     */
    private function getPagamentosNoPeriodo(): Collection
    {
        return Pagamento::query()
            ->whereHas('contrato', fn($q) => $q->where('locador_id', $this->locador->id))
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Pagamentos pagos no periodo
                    $q->where('status', StatusPagamento::Pago)
                        ->whereBetween('data_pagamento', [$this->dataInicio, $this->dataFim]);
                })->orWhere(function ($q) {
                    // Pagamentos com vencimento no periodo (pendentes/atrasados)
                    $q->whereIn('status', [StatusPagamento::Pendente, StatusPagamento::Atrasado])
                        ->whereBetween('data_vencimento', [$this->dataInicio, $this->dataFim]);
                });
            })
            ->with(['contrato.locatario', 'contrato.itens.tipoAtivo'])
            ->get();
    }

    /**
     * Retorna pagamentos inadimplentes (atrasados ou pendentes vencidos).
     */
    private function getPagamentosInadimplentes(): Collection
    {
        $hoje = Carbon::now()->startOfDay();

        return Pagamento::query()
            ->whereHas('contrato', fn($q) => $q->where('locador_id', $this->locador->id))
            ->where(function ($query) use ($hoje) {
                $query->where('status', StatusPagamento::Atrasado)
                    ->orWhere(function ($q) use ($hoje) {
                        $q->where('status', StatusPagamento::Pendente)
                            ->where('data_vencimento', '<', $hoje);
                    });
            })
            ->with(['contrato.locatario'])
            ->get();
    }

    /**
     * Calcula valor total inadimplente.
     */
    private function getValorInadimplente(Collection $pagamentos): float
    {
        $hoje = Carbon::now()->startOfDay();

        return $pagamentos->filter(function ($pagamento) use ($hoje) {
            if ($pagamento->status === StatusPagamento::Atrasado) {
                return true;
            }
            if ($pagamento->status === StatusPagamento::Pendente && $pagamento->data_vencimento < $hoje) {
                return true;
            }
            return false;
        })->sum(fn($p) => $this->valorFinal($p));
    }

    /**
     * Calcula valor final do pagamento (valor - desconto).
     */
    private function valorFinal(Pagamento $pagamento): float
    {
        return (float) $pagamento->valor - (float) ($pagamento->desconto_comercial ?? 0);
    }
}
