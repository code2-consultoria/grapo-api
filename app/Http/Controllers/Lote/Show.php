<?php

namespace App\Http\Controllers\Lote;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    /**
     * Exibe detalhes do lote.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Lote::with('tipoAtivo');

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $lote = $query->findOrFail($id);

        return response()->json([
            'data' => $lote,
        ]);
    }
}
