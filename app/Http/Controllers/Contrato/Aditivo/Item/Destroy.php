<?php

namespace App\Http\Controllers\Contrato\Aditivo\Item;

use App\Actions\Contrato\Aditivo\RemoverItem;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    /**
     * Remove um item do aditivo.
     */
    public function __invoke(Request $request, string $contratoId, string $aditivoId, string $itemId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $aditivo = $contrato->aditivos()->findOrFail($aditivoId);
        $item = $aditivo->itens()->findOrFail($itemId);

        $remover = new RemoverItem($item);
        $remover->handle();

        return response()->json([
            'message' => 'Item removido do aditivo.',
        ]);
    }
}
