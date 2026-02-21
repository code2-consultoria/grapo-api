<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Queries\Dashboard\Metricas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Retorna metricas do dashboard.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        if (! $locador) {
            return response()->json([
                'message' => 'Usuario nao vinculado a um locador.',
            ], 403);
        }

        $query = new Metricas($locador);
        $metricas = $query->handle();

        return response()->json([
            'data' => $metricas,
        ]);
    }
}
