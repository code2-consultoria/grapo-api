<?php

namespace App\Http\Controllers\Contrato;

use App\Actions\Contrato\Finalizar as FinalizarAction;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Finalizar extends Controller
{
    /**
     * Finaliza um contrato.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Contrato::with('itens.alocacoes');

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $contrato = $query->findOrFail($id);

        $finalizar = new FinalizarAction($contrato);
        $finalizar->handle();

        return response()->json([
            'data' => $finalizar->getContrato()->load(['locador', 'locatario', 'itens.tipoAtivo']),
            'message' => 'Contrato finalizado com sucesso.',
        ]);
    }
}
