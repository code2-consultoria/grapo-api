<?php

namespace App\Http\Controllers\TipoAtivo;

use App\Http\Controllers\Controller;
use App\Queries\TipoAtivo\Obter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    /**
     * Exibe detalhes do tipo de ativo.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $locador = $user->isCliente() ? $user->locador() : null;

        $query = new Obter(
            id: $id,
            locador: $locador
        );

        $tipoAtivo = $query->handle();

        return response()->json([
            'data' => $tipoAtivo,
        ]);
    }
}
