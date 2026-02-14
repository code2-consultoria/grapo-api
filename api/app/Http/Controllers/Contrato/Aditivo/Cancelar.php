<?php

namespace App\Http\Controllers\Contrato\Aditivo;

use App\Actions\Contrato\Aditivo\Cancelar as CancelarAction;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Cancelar extends Controller
{
    /**
     * Cancela um aditivo e reverte as alterações se estiver ativo.
     */
    public function __invoke(Request $request, string $contratoId, string $aditivoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $aditivo = $contrato->aditivos()->findOrFail($aditivoId);

        $cancelar = new CancelarAction($aditivo);
        $cancelar->handle();
        $aditivo = $cancelar->getAditivo();

        return response()->json([
            'data' => $aditivo->load(['itens.tipoAtivo']),
            'message' => 'Aditivo cancelado com sucesso.',
        ]);
    }
}
