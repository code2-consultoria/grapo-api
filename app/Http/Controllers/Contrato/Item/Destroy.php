<?php

namespace App\Http\Controllers\Contrato\Item;

use App\Actions\Contrato\Item\Remover;
use App\Exceptions\ContratoAtivoImutavelException;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\ContratoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    /**
     * Remove um item do contrato.
     */
    public function __invoke(Request $request, string $contratoId, string $itemId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Contrato::query();

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $contrato = $query->findOrFail($contratoId);
        $item = ContratoItem::where('contrato_id', $contrato->id)->findOrFail($itemId);

        try {
            $remover = new Remover($item);
            $remover->handle();

            return response()->json([
                'message' => 'Item removido do contrato.',
            ]);
        } catch (ContratoAtivoImutavelException $e) {
            return $e->render($request);
        }
    }
}
