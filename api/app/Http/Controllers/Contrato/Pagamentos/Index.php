<?php

namespace App\Http\Controllers\Contrato\Pagamentos;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    public function __invoke(Request $request, string $contratoId): JsonResponse
    {
        $contrato = Contrato::findOrFail($contratoId);

        $pagamentos = $contrato->pagamentos()
            ->orderBy('data_vencimento')
            ->get();

        return response()->json([
            'data' => $pagamentos->map(fn ($p) => [
                'id' => $p->id,
                'valor' => number_format($p->valor, 2, '.', ''),
                'desconto_comercial' => number_format($p->desconto_comercial, 2, '.', ''),
                'valor_final' => $p->valor_final,
                'data_vencimento' => $p->data_vencimento->format('Y-m-d'),
                'data_pagamento' => $p->data_pagamento?->format('Y-m-d'),
                'status' => $p->status->value,
                'status_label' => $p->status->label(),
                'origem' => $p->origem->value,
                'origem_label' => $p->origem->label(),
                'stripe_payment_id' => $p->stripe_payment_id,
                'observacoes' => $p->observacoes,
            ]),
        ]);
    }
}
