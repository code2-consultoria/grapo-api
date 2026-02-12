<?php

namespace App\Http\Controllers\Lote;

use App\Actions\Lote\Excluir;
use App\Exceptions\LoteComAlocacoesException;
use App\Http\Controllers\Controller;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    /**
     * Exclui um lote.
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

        try {
            $excluir = new Excluir($lote);
            $excluir->handle();

            return response()->json([
                'message' => 'Lote excluído com sucesso.',
            ]);
        } catch (LoteComAlocacoesException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
