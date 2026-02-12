<?php

namespace App\Http\Controllers\Lote;

use App\Actions\Lote\Atualizar;
use App\Http\Controllers\Controller;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update extends Controller
{
    /**
     * Atualiza um lote.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Lote::query();

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $lote = $query->findOrFail($id);

        $validated = $request->validate([
            'valor_unitario_diaria' => ['sometimes', 'numeric', 'min:0'],
            'custo_aquisicao' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:disponivel,indisponivel,baixado'],
        ]);

        $atualizar = new Atualizar($lote, $validated);
        $atualizar->handle();

        return response()->json([
            'data' => $atualizar->getLote()->load('tipoAtivo'),
            'message' => 'Lote atualizado com sucesso.',
        ]);
    }
}
