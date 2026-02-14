<?php

namespace App\Http\Controllers\Contrato\Item;

use App\Actions\Contrato\Item\Atualizar;
use App\Exceptions\ContratoAtivoImutavelException;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\ContratoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update extends Controller
{
    /**
     * Atualiza um item do contrato.
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

        $validated = $request->validate([
            'quantidade' => ['sometimes', 'integer', 'min:1'],
            'valor_unitario' => ['sometimes', 'numeric', 'min:0'],
            'periodo_aluguel' => ['sometimes', 'string', 'in:diaria,mensal'],
        ]);

        try {
            $atualizar = new Atualizar(
                item: $item,
                quantidade: $validated['quantidade'] ?? null,
                valorUnitario: $validated['valor_unitario'] ?? null,
                periodoAluguel: $validated['periodo_aluguel'] ?? null
            );
            $atualizar->handle();

            return response()->json([
                'data' => $atualizar->getItem()->load('tipoAtivo'),
                'message' => 'Item atualizado.',
            ]);
        } catch (ContratoAtivoImutavelException $e) {
            return $e->render($request);
        }
    }
}
