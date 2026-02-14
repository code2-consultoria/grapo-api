<?php

namespace App\Http\Controllers\Contrato\TipoCobranca;

use App\Enums\TipoCobranca;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Update extends Controller
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        if ($contrato->estaAtivo()) {
            return response()->json([
                'message' => 'Tipo de cobranca nao pode ser alterado para contratos ativos.',
            ], 400);
        }

        $validated = $request->validate([
            'tipo_cobranca' => ['required', Rule::enum(TipoCobranca::class)],
        ]);

        $contrato->update([
            'tipo_cobranca' => $validated['tipo_cobranca'],
        ]);

        return response()->json([
            'data' => [
                'id' => $contrato->id,
                'tipo_cobranca' => $contrato->tipo_cobranca->value,
                'tipo_cobranca_label' => $contrato->tipo_cobranca->label(),
            ],
        ]);
    }
}
