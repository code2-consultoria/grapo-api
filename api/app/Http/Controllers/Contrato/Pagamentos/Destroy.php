<?php

namespace App\Http\Controllers\Contrato\Pagamentos;

use App\Enums\StatusPagamento;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Pagamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    public function __invoke(Request $request, string $contratoId, string $pagamentoId): JsonResponse
    {
        $contrato = Contrato::findOrFail($contratoId);
        $pagamento = $contrato->pagamentos()->findOrFail($pagamentoId);

        if ($pagamento->status === StatusPagamento::Pago) {
            return response()->json([
                'message' => 'Pagamento ja realizado nao pode ser cancelado.',
            ], 400);
        }

        if ($pagamento->status === StatusPagamento::Cancelado) {
            return response()->json([
                'message' => 'Pagamento ja esta cancelado.',
            ], 400);
        }

        $pagamento->update([
            'status' => StatusPagamento::Cancelado,
        ]);

        return response()->json([
            'message' => 'Pagamento cancelado com sucesso.',
            'data' => [
                'id' => $pagamento->id,
                'status' => $pagamento->status->value,
                'status_label' => $pagamento->status->label(),
            ],
        ]);
    }
}
