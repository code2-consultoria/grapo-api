<?php

namespace App\Http\Controllers\Contrato;

use App\Http\Controllers\Controller;
use App\Queries\Contrato\Listar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Lista contratos do locador.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->isCliente() ? $user->locador() : null;

        $query = new Listar(
            locador: $locador,
            status: $request->input('status'),
            locatarioId: $request->input('locatario_id'),
            search: $request->input('search')
        );

        $contratos = $query->handle();

        return response()->json([
            'data' => $contratos->items(),
            'meta' => [
                'current_page' => $contratos->currentPage(),
                'last_page' => $contratos->lastPage(),
                'per_page' => $contratos->perPage(),
                'total' => $contratos->total(),
            ],
        ]);
    }
}
