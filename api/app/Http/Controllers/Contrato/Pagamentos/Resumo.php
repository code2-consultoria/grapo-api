<?php

namespace App\Http\Controllers\Contrato\Pagamentos;

use App\Enums\StatusPagamento;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Resumo extends Controller
{
    public function __invoke(Request $request, string $contratoId): JsonResponse
    {
        $contrato = Contrato::findOrFail($contratoId);
        $pagamentos = $contrato->pagamentos;

        $totalPago = $pagamentos
            ->where('status', StatusPagamento::Pago)
            ->sum('valor');

        $totalPendente = $pagamentos
            ->where('status', StatusPagamento::Pendente)
            ->sum('valor');

        $totalAtrasado = $pagamentos
            ->where('status', StatusPagamento::Atrasado)
            ->sum('valor');

        $qtdPagos = $pagamentos
            ->where('status', StatusPagamento::Pago)
            ->count();

        $qtdPendentes = $pagamentos
            ->where('status', StatusPagamento::Pendente)
            ->count();

        $qtdAtrasados = $pagamentos
            ->where('status', StatusPagamento::Atrasado)
            ->count();

        return response()->json([
            'data' => [
                'total_contrato' => number_format($contrato->valor_total, 2, '.', ''),
                'total_pago' => number_format($totalPago, 2, '.', ''),
                'total_pendente' => number_format($totalPendente, 2, '.', ''),
                'total_atrasado' => number_format($totalAtrasado, 2, '.', ''),
                'qtd_pagamentos' => $pagamentos->count(),
                'qtd_pagos' => $qtdPagos,
                'qtd_pendentes' => $qtdPendentes,
                'qtd_atrasados' => $qtdAtrasados,
            ],
        ]);
    }
}
