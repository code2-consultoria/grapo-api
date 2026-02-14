<?php

namespace App\Http\Controllers\TipoAtivo;

use App\Http\Controllers\Controller;
use App\Queries\TipoAtivo\Listar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Lista tipos de ativos do locador.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->isCliente() ? $user->locador() : null;

        $query = new Listar(
            locador: $locador,
            search: $request->input('search')
        );

        $tiposAtivos = $query->handle();

        // Adiciona quantidade disponível em cada item
        $items = collect($tiposAtivos->items())->map(function ($tipoAtivo) {
            $tipoAtivo->quantidade_disponivel = $tipoAtivo->quantidadeDisponivel();
            return $tipoAtivo;
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $tiposAtivos->currentPage(),
                'last_page' => $tiposAtivos->lastPage(),
                'per_page' => $tiposAtivos->perPage(),
                'total' => $tiposAtivos->total(),
            ],
        ]);
    }
}
