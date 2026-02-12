<?php

namespace App\Http\Controllers\Pessoa;

use App\Actions\Pessoa\Atualizar;
use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update extends Controller
{
    /**
     * Atualiza uma pessoa.
     */
    public function __invoke(Request $request, Pessoa $pessoa): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        // Verifica autorização
        if ($user->isCliente() && $locador) {
            // Pode editar apenas pessoas vinculadas ao seu locador (não o próprio locador)
            if ($pessoa->locador_id !== $locador->id) {
                abort(404);
            }
        }

        $validated = $request->validate([
            'nome' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        $atualizar = new Atualizar($pessoa, $validated);
        $atualizar->handle();

        return response()->json([
            'data' => $atualizar->getPessoa()->load('documentos'),
            'message' => 'Pessoa atualizada com sucesso.',
        ]);
    }
}
