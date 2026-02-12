<?php

namespace App\Http\Controllers\TipoAtivo;

use App\Actions\TipoAtivo\Excluir;
use App\Http\Controllers\Controller;
use App\Models\TipoAtivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    /**
     * Exclui um tipo de ativo.
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

        $excluir = new Excluir($tipoAtivo);
        $excluir->handle();

        return response()->json([
            'message' => 'Tipo de ativo excluído com sucesso.',
        ]);
    }
}
