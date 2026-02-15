<?php

namespace App\Http\Controllers\Lote\Rentabilidade;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Queries\Lote\Rentabilidade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    /**
     * Retorna os dados de rentabilidade do lote.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $lote = Lote::with('tipoAtivo')->findOrFail($id);

        // Verifica se o lote pertence ao locador do usuario
        if ($user->isCliente() && $locador && $lote->locador_id !== $locador->id) {
            return response()->json([
                'message' => 'Acesso negado. Este lote pertence a outro locador.',
            ], 403);
        }

        $query = new Rentabilidade($lote);

        return response()->json($query->handle());
    }
}
