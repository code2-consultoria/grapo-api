<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    /**
     * Remove uma pessoa (soft delete via ativo).
     */
    public function __invoke(Request $request, Pessoa $pessoa): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        // Verifica autorização
        if ($user->isCliente() && $locador) {
            // Pode desativar apenas pessoas vinculadas ao seu locador (não o próprio locador)
            if ($pessoa->locador_id !== $locador->id) {
                abort(404);
            }
        }

        $pessoa->ativo = false;
        $pessoa->save();

        return response()->json([
            'message' => 'Pessoa desativada com sucesso.',
        ]);
    }
}
