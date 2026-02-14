<?php

namespace App\Queries\Dashboard;

use App\Contracts\Query;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Query para obter metricas do dashboard.
 */
class Metricas implements Query
{
    public function __construct(
        private Pessoa $locador
    ) {}

    /**
     * Executa a query e retorna as metricas.
     */
    public function handle(): array
    {
        return [
            'financeiro' => $this->getMetricasFinanceiras(),
            'operacional' => $this->getMetricasOperacionais(),
            'alertas' => $this->getAlertas(),
        ];
    }

    /**
     * Metricas financeiras.
     */
    private function getMetricasFinanceiras(): array
    {
        $contratosAtivos = Contrato::where('locador_id', $this->locador->id)
            ->where('status', 'ativo')
            ->get();

        $receitaTotal = $contratosAtivos->sum('valor_total');
        $quantidadeAtivos = $contratosAtivos->count();

        // Contratos que vencem nos proximos 30 dias
        $dataLimite = Carbon::now()->addDays(30);
        $contratosAVencer = Contrato::where('locador_id', $this->locador->id)
            ->where('status', 'ativo')
            ->where('data_termino', '<=', $dataLimite)
            ->orderBy('data_termino')
            ->get(['id', 'codigo', 'data_termino', 'valor_total', 'locatario_id'])
            ->map(function ($contrato) {
                $diasRestantes = Carbon::now()->diffInDays(Carbon::parse($contrato->data_termino), false);
                return [
                    'id' => $contrato->id,
                    'codigo' => $contrato->codigo,
                    'data_termino' => $contrato->data_termino,
                    'dias_restantes' => max(0, $diasRestantes),
                    'valor_total' => $contrato->valor_total,
                    'locatario' => $contrato->locatario?->nome,
                ];
            });

        return [
            'receita_total' => round($receitaTotal, 2),
            'contratos_ativos' => $quantidadeAtivos,
            'receita_media_mensal' => $quantidadeAtivos > 0 ? round($receitaTotal / 12, 2) : 0,
            'contratos_a_vencer' => $contratosAVencer->toArray(),
        ];
    }

    /**
     * Metricas operacionais.
     */
    private function getMetricasOperacionais(): array
    {
        // Estoque
        $lotes = Lote::where('locador_id', $this->locador->id)->get();
        $estoqueTotal = $lotes->sum('quantidade_total');
        $estoqueDisponivel = $lotes->sum('quantidade_disponivel');
        $estoqueAlocado = $estoqueTotal - $estoqueDisponivel;
        $taxaOcupacao = $estoqueTotal > 0 ? round(($estoqueAlocado / $estoqueTotal) * 100, 1) : 0;

        // Lotes por status
        $lotesPorStatus = [
            'disponivel' => $lotes->where('status', 'disponivel')->count(),
            'indisponivel' => $lotes->where('status', 'indisponivel')->count(),
            'esgotado' => $lotes->where('status', 'esgotado')->count(),
        ];

        // Top 5 ativos mais alugados (por quantidade em contratos ativos)
        $topAtivos = ContratoItem::query()
            ->join('contratos', 'contrato_itens.contrato_id', '=', 'contratos.id')
            ->join('tipos_ativos', 'contrato_itens.tipo_ativo_id', '=', 'tipos_ativos.id')
            ->where('contratos.locador_id', $this->locador->id)
            ->where('contratos.status', 'ativo')
            ->selectRaw('tipos_ativos.id, tipos_ativos.nome, SUM(contrato_itens.quantidade) as quantidade_total')
            ->groupBy('tipos_ativos.id', 'tipos_ativos.nome')
            ->orderByDesc('quantidade_total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nome' => $item->nome,
                    'quantidade' => (int) $item->quantidade_total,
                ];
            });

        return [
            'estoque_total' => $estoqueTotal,
            'estoque_disponivel' => $estoqueDisponivel,
            'estoque_alocado' => $estoqueAlocado,
            'taxa_ocupacao' => $taxaOcupacao,
            'lotes_por_status' => $lotesPorStatus,
            'top_ativos' => $topAtivos->toArray(),
        ];
    }

    /**
     * Alertas do sistema.
     */
    private function getAlertas(): array
    {
        $alertas = [];

        // Contratos vencendo em 7 dias
        $dataLimite = Carbon::now()->addDays(7);
        $contratosUrgentes = Contrato::where('locador_id', $this->locador->id)
            ->where('status', 'ativo')
            ->where('data_termino', '<=', $dataLimite)
            ->count();

        if ($contratosUrgentes > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Contratos a vencer',
                'mensagem' => "{$contratosUrgentes} contrato(s) vence(m) nos proximos 7 dias",
                'icone' => 'calendar',
            ];
        }

        // Ativos com estoque baixo (menos de 5 unidades disponiveis)
        $tiposAtivos = TipoAtivo::where('locador_id', $this->locador->id)->get();
        $ativosEstoqueBaixo = [];

        foreach ($tiposAtivos as $tipoAtivo) {
            $disponivel = $tipoAtivo->quantidadeDisponivel();
            if ($disponivel > 0 && $disponivel < 5) {
                $ativosEstoqueBaixo[] = [
                    'nome' => $tipoAtivo->nome,
                    'disponivel' => $disponivel,
                ];
            }
        }

        if (count($ativosEstoqueBaixo) > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Estoque baixo',
                'mensagem' => count($ativosEstoqueBaixo) . ' ativo(s) com menos de 5 unidades',
                'icone' => 'package',
                'detalhes' => $ativosEstoqueBaixo,
            ];
        }

        // Ativos esgotados
        $ativosEsgotados = [];
        foreach ($tiposAtivos as $tipoAtivo) {
            if ($tipoAtivo->quantidadeDisponivel() === 0 && $tipoAtivo->lotes()->exists()) {
                $ativosEsgotados[] = $tipoAtivo->nome;
            }
        }

        if (count($ativosEsgotados) > 0) {
            $alertas[] = [
                'tipo' => 'destructive',
                'titulo' => 'Ativos esgotados',
                'mensagem' => count($ativosEsgotados) . ' ativo(s) sem estoque disponivel',
                'icone' => 'alert-triangle',
                'detalhes' => $ativosEsgotados,
            ];
        }

        return $alertas;
    }
}
