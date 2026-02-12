<?php

namespace App\Http\Controllers\Auth\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Me extends Controller
{
    /**
     * Retorna os dados do usuário autenticado.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'papel' => $user->papel,
                'locador' => $locador ? [
                    'id' => $locador->id,
                    'nome' => $locador->nome,
                ] : null,
            ],
        ]);
    }
}
