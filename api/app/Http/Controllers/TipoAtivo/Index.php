<?php

namespace App\Http\Controllers\TipoAtivo;

use App\Http\Controllers\Controller;
use App\Models\TipoAtivo;
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
        $locador = $user->locador();

        $query = TipoAtivo::query();

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nome', 'ilike', "%{$search}%");
        }

        $tiposAtivos = $query->orderBy('nome')->paginate(15);

        return response()->json([
            'data' => $tiposAtivos->items(),
            'meta' => [
                'current_page' => $tiposAtivos->currentPage(),
                'last_page' => $tiposAtivos->lastPage(),
                'per_page' => $tiposAtivos->perPage(),
                'total' => $tiposAtivos->total(),
            ],
        ]);
    }
}
