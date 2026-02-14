<?php

namespace App\Http\Controllers\Contrato\Pagamentos;

use App\Enums\OrigemPagamento;
use App\Enums\StatusPagamento;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Pagamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    public function __invoke(Request $request, string $contratoId): JsonResponse
    {
        $contrato = Contrato::findOrFail($contratoId);

        $validated = $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'desconto_comercial' => 'nullable|numeric|min:0|lte:valor',
            'data_vencimento' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'origem' => ['required', Rule::enum(OrigemPagamento::class)],
            'observacoes' => 'nullable|string|max:500',
        ]);

        // Define status baseado na data de pagamento
        $status = StatusPagamento::Pendente;
        if (! empty($validated['data_pagamento'])) {
            $status = StatusPagamento::Pago;
        }

        $pagamento = new Pagamento([
            'valor' => $validated['valor'],
            'desconto_comercial' => $validated['desconto_comercial'] ?? 0,
            'data_vencimento' => $validated['data_vencimento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'status' => $status,
            'origem' => $validated['origem'],
            'observacoes' => $validated['observacoes'] ?? null,
        ]);
        $pagamento->contrato()->associate($contrato);
        $pagamento->save();

        return response()->json([
            'data' => [
                'id' => $pagamento->id,
                'valor' => number_format($pagamento->valor, 2, '.', ''),
                'desconto_comercial' => number_format($pagamento->desconto_comercial, 2, '.', ''),
                'valor_final' => $pagamento->valor_final,
                'data_vencimento' => $pagamento->data_vencimento->format('Y-m-d'),
                'data_pagamento' => $pagamento->data_pagamento?->format('Y-m-d'),
                'status' => $pagamento->status->value,
                'status_label' => $pagamento->status->label(),
                'origem' => $pagamento->origem->value,
                'origem_label' => $pagamento->origem->label(),
                'observacoes' => $pagamento->observacoes,
            ],
        ], 201);
    }
}
