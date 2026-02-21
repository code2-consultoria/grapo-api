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
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'papel' => $user->papel,
                    'ativo' => $user->ativo,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'locador' => $locador ? [
                    'id' => $locador->id,
                    'tipo' => $locador->tipo,
                    'nome' => $locador->nome,
                    'email' => $locador->email,
                    'telefone' => $locador->telefone,
                    'ativo' => $locador->ativo,
                    'data_limite_acesso' => $locador->data_limite_acesso?->format('Y-m-d'),
                    'has_acesso_ativo' => $locador->hasAcessoAtivo(),
                ] : null,
            ],
        ]);
    }
}
