<?php

namespace App\Http\Controllers\Lote;

use App\Http\Controllers\Controller;
use App\Queries\Lote\Listar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Lista lotes do locador.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->isCliente() ? $user->locador() : null;

        $query = new Listar(
            locador: $locador,
            tipoAtivoId: $request->input('tipo_ativo_id'),
            status: $request->input('status'),
            search: $request->input('search')
        );

        $lotes = $query->handle();

        return response()->json([
            'data' => $lotes->items(),
            'meta' => [
                'current_page' => $lotes->currentPage(),
                'last_page' => $lotes->lastPage(),
                'per_page' => $lotes->perPage(),
                'total' => $lotes->total(),
            ],
        ]);
    }
}
