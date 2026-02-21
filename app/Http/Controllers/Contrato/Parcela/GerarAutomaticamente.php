<?php

namespace App\Http\Controllers\Contrato\Parcela;

use App\Actions\Contrato\GerarParcelas;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;

class GerarAutomaticamente extends Controller
{
    public function __invoke(string $contratoId): JsonResponse
    {
        $contrato = Contrato::findOrFail($contratoId);

        // Valida se contrato esta em rascunho
        if (! $contrato->estaEmRascunho()) {
            return response()->json([
                'message' => 'Parcelas so podem ser geradas para contratos em rascunho.',
            ], 400);
        }

        // Valida se contrato e recorrente manual
        if (! $contrato->temCobrancaManual()) {
            return response()->json([
                'message' => 'Geracao automatica de parcelas so e permitida para contratos com cobranca recorrente manual.',
            ], 400);
        }

        // Valida se ja existem parcelas
        if ($contrato->pagamentos()->exists()) {
            return response()->json([
                'message' => 'O contrato ja possui parcelas cadastradas.',
            ], 400);
        }

        // Gera as parcelas
        $action = new GerarParcelas($contrato);
        $action->handle();

        return response()->json([
            'message' => 'Parcelas geradas com sucesso.',
            'data' => [
                'total_parcelas' => $action->getTotalParcelas(),
                'valor_parcela' => $contrato->calcularValorMensal(),
                'parcelas' => $action->getParcelasGeradas()->map(fn ($p) => [
                    'id' => $p->id,
                    'valor' => $p->valor,
                    'data_vencimento' => $p->data_vencimento->format('Y-m-d'),
                    'observacoes' => $p->observacoes,
                ]),
            ],
        ]);
    }
}
