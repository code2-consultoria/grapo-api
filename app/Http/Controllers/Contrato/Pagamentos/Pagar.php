<?php

namespace App\Http\Controllers\Contrato\Pagamentos;

use App\Enums\StatusPagamento;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Pagamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Pagar extends Controller
{
    public function __invoke(Request $request, string $contratoId, string $pagamentoId): JsonResponse
    {
        $contrato = Contrato::findOrFail($contratoId);
        $pagamento = $contrato->pagamentos()->findOrFail($pagamentoId);

        if ($pagamento->status === StatusPagamento::Pago) {
            return response()->json([
                'message' => 'Pagamento ja foi realizado.',
            ], 400);
        }

        if ($pagamento->status === StatusPagamento::Cancelado) {
            return response()->json([
                'message' => 'Pagamento cancelado nao pode ser marcado como pago.',
            ], 400);
        }

        $validated = $request->validate([
            'data_pagamento' => 'nullable|date',
            'observacoes' => 'nullable|string|max:500',
        ]);

        $pagamento->update([
            'status' => StatusPagamento::Pago,
            'data_pagamento' => $validated['data_pagamento'] ?? now()->format('Y-m-d'),
            'observacoes' => $validated['observacoes'] ?? $pagamento->observacoes,
        ]);

        return response()->json([
            'data' => [
                'id' => $pagamento->id,
                'valor' => number_format($pagamento->valor, 2, '.', ''),
                'data_vencimento' => $pagamento->data_vencimento->format('Y-m-d'),
                'data_pagamento' => $pagamento->data_pagamento->format('Y-m-d'),
                'status' => $pagamento->status->value,
                'status_label' => $pagamento->status->label(),
                'origem' => $pagamento->origem->value,
                'origem_label' => $pagamento->origem->label(),
                'observacoes' => $pagamento->observacoes,
            ],
        ]);
    }
}
