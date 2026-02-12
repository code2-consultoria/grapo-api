<?php

namespace App\Http\Controllers\Contrato;

use App\Actions\Contrato\Cancelar as CancelarAction;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Cancelar extends Controller
{
    /**
     * Cancela um contrato.
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

        $cancelar = new CancelarAction($contrato);
        $cancelar->handle();

        return response()->json([
            'data' => $cancelar->getContrato()->load(['locador', 'locatario', 'itens.tipoAtivo']),
            'message' => 'Contrato cancelado com sucesso.',
        ]);
    }
}
