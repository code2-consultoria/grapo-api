<?php

namespace App\Http\Controllers\Relatorio\Financeiro;

use App\Http\Controllers\Controller;
use App\Queries\Relatorio\Financeiro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    /**
     * Retorna os dados do relatorio financeiro.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        if (!$locador) {
            return response()->json([
                'message' => 'Locador nao encontrado.',
            ], 403);
        }

        $dataInicio = $request->query('data_inicio');
        $dataFim = $request->query('data_fim');

        $query = new Financeiro($locador, $dataInicio, $dataFim);

        return response()->json($query->handle());
    }
}
