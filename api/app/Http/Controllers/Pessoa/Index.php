<?php

namespace App\Http\Controllers\Pessoa;

use App\Enums\TipoPessoa;
use App\Http\Controllers\Controller;
use App\Queries\Pessoa\Listar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Lista pessoas.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->isCliente() ? $user->locador() : null;

        // Tipo pode vir da rota (defaults) ou query string
        $tipoParam = $request->route('tipo') ?? $request->input('tipo');
        $tipo = $tipoParam ? TipoPessoa::tryFrom($tipoParam) : null;

        $query = new Listar(
            locador: $locador,
            tipo: $tipo,
            search: $request->input('search'),
            ativo: $request->has('ativo') ? $request->boolean('ativo') : null
        );

        $pessoas = $query->handle();

        return response()->json([
            'data' => $pessoas->items(),
            'meta' => [
                'current_page' => $pessoas->currentPage(),
                'last_page' => $pessoas->lastPage(),
                'per_page' => $pessoas->perPage(),
                'total' => $pessoas->total(),
            ],
        ]);
    }
}
