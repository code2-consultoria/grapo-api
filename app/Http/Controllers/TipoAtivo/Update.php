<?php

namespace App\Http\Controllers\TipoAtivo;

use App\Actions\TipoAtivo\Atualizar;
use App\Http\Controllers\Controller;
use App\Models\TipoAtivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update extends Controller
{
    /**
     * Atualiza um tipo de ativo.
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

        $validated = $request->validate([
            'nome' => ['sometimes', 'string', 'max:255'],
            'descricao' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'unidade_medida' => ['sometimes', 'string', 'max:50'],
            'valor_mensal_sugerido' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        $atualizar = new Atualizar($tipoAtivo, $validated);
        $atualizar->handle();

        return response()->json([
            'data' => $atualizar->getTipoAtivo(),
            'message' => 'Tipo de ativo atualizado com sucesso.',
        ]);
    }
}
