<?php

namespace App\Http\Controllers\Lote;

use App\Http\Controllers\Controller;
use App\Models\Lote;
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
        $locador = $user->locador();

        $query = Lote::with('tipoAtivo');

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        // Filtros
        if ($request->has('tipo_ativo_id')) {
            $query->where('tipo_ativo_id', $request->input('tipo_ativo_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('codigo', 'ilike', "%{$search}%");
        }

        $lotes = $query->orderBy('data_aquisicao', 'desc')->paginate(15);

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
