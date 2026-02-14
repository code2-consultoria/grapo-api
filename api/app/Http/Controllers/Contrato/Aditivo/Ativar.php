<?php

namespace App\Http\Controllers\Contrato\Aditivo;

use App\Actions\Contrato\Aditivo\Ativar as AtivarAction;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Ativar extends Controller
{
    /**
     * Ativa um aditivo e aplica as alterações ao contrato.
     */
    public function __invoke(Request $request, string $contratoId, string $aditivoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $aditivo = $contrato->aditivos()->findOrFail($aditivoId);

        $ativar = new AtivarAction($aditivo);
        $ativar->handle();
        $aditivo = $ativar->getAditivo();

        return response()->json([
            'data' => $aditivo->load(['itens.tipoAtivo']),
            'message' => 'Aditivo ativado com sucesso.',
        ]);
    }
}
