<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    /**
     * Exibe uma pessoa.
     */
    public function __invoke(Request $request, Pessoa $pessoa): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        // Verifica autorização
        if ($user->isCliente() && $locador) {
            // Pode ver o próprio locador ou pessoas vinculadas ao seu locador
            $autorizado = $pessoa->id === $locador->id
                || $pessoa->locador_id === $locador->id;

            if (! $autorizado) {
                abort(404);
            }
        }

        return response()->json([
            'data' => $pessoa->load('documentos'),
        ]);
    }
}
