<?php

namespace App\Http\Controllers\TipoAtivo;

use App\Http\Controllers\Controller;
use App\Models\TipoAtivo;
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
        $locador = $user->locador();

        $query = TipoAtivo::query();

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $tipoAtivo = $query->findOrFail($id);

        // Adiciona quantidade disponível
        $tipoAtivo->quantidade_disponivel = $tipoAtivo->quantidadeDisponivel();

        return response()->json([
            'data' => $tipoAtivo,
        ]);
    }
}
