<?php

namespace App\Actions\Contrato;

use App\Contracts\Command;
use App\Enums\OrigemPagamento;
use App\Enums\StatusPagamento;
use App\Models\Contrato;
use App\Models\Pagamento;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Gera parcelas automaticamente para um contrato recorrente manual.
 */
class GerarParcelas implements Command
{
    private Collection $parcelasGeradas;

    public function __construct(
        private Contrato $contrato
    ) {
        $this->parcelasGeradas = collect();
    }

    /**
     * Gera as parcelas baseado no periodo do contrato.
     */
    public function handle(): void
    {
        $meses = $this->contrato->calcularMesesLocacao();
        $valorMensal = $this->contrato->calcularValorMensal();
        $diaVencimento = $this->contrato->data_inicio->day;

        DB::transaction(function () use ($meses, $valorMensal, $diaVencimento) {
            for ($i = 0; $i < $meses; $i++) {
                $dataVencimento = $this->calcularDataVencimento(
                    $this->contrato->data_inicio,
                    $i,
                    $diaVencimento
                );

                $parcela = new Pagamento([
                    'valor' => $valorMensal,
                    'data_vencimento' => $dataVencimento,
                    'status' => StatusPagamento::Pendente,
                    'origem' => OrigemPagamento::Manual,
                    'observacoes' => 'Parcela '.($i + 1)." de {$meses}",
                ]);

                $parcela->contrato()->associate($this->contrato);
                $parcela->save();

                $this->parcelasGeradas->push($parcela);
            }
        });
    }

    /**
     * Calcula a data de vencimento para uma parcela.
     * Se o dia nao existir no mes (ex: 31 de fevereiro), usa o ultimo dia do mes.
     */
    private function calcularDataVencimento(CarbonInterface $dataInicio, int $mesesAdicionar, int $dia): Carbon
    {
        // Calcula ano e mes de destino
        $ano = $dataInicio->year;
        $mes = $dataInicio->month + $mesesAdicionar;

        // Ajusta ano se mes ultrapassar 12
        while ($mes > 12) {
            $mes -= 12;
            $ano++;
        }

        // Verifica quantos dias tem no mes de destino
        $ultimoDiaMes = Carbon::createFromDate($ano, $mes, 1)->daysInMonth;

        // Se o dia desejado for maior que o ultimo dia do mes, usa o ultimo dia
        $diaFinal = min($dia, $ultimoDiaMes);

        return Carbon::createFromDate($ano, $mes, $diaFinal);
    }

    /**
     * Retorna as parcelas geradas.
     */
    public function getParcelasGeradas(): Collection
    {
        return $this->parcelasGeradas;
    }

    /**
     * Retorna o total de parcelas geradas.
     */
    public function getTotalParcelas(): int
    {
        return $this->parcelasGeradas->count();
    }
}
