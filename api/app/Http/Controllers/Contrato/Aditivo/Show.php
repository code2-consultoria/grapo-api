<?php

namespace App\Http\Controllers\Contrato\Aditivo;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    /**
     * Exibe um aditivo específico.
     */
    public function __invoke(Request $request, string $contratoId, string $aditivoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $aditivo = $contrato->aditivos()
            ->with(['itens.tipoAtivo'])
            ->findOrFail($aditivoId);

        return response()->json([
            'data' => $aditivo,
        ]);
    }
}
