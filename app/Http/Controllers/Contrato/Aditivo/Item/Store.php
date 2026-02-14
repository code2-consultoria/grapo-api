<?php

namespace App\Http\Controllers\Contrato\Aditivo\Item;

use App\Actions\Contrato\Aditivo\AdicionarItem;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\TipoAtivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    /**
     * Adiciona um item ao aditivo.
     */
    public function __invoke(Request $request, string $contratoId, string $aditivoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $aditivo = $contrato->aditivos()->findOrFail($aditivoId);

        $validated = $request->validate([
            'tipo_ativo_id' => [
                'required',
                'uuid',
                Rule::exists('tipos_ativos', 'id')->where(function ($query) use ($locador) {
                    $query->where('locador_id', $locador->id);
                }),
            ],
            'quantidade_alterada' => ['required', 'integer', 'not_in:0'],
            'valor_unitario' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tipoAtivo = TipoAtivo::findOrFail($validated['tipo_ativo_id']);

        $adicionar = new AdicionarItem(
            aditivo: $aditivo,
            tipoAtivo: $tipoAtivo,
            quantidadeAlterada: $validated['quantidade_alterada'],
            valorUnitario: $validated['valor_unitario'] ?? null,
        );
        $adicionar->handle();
        $item = $adicionar->getItem();

        return response()->json([
            'data' => $item->load(['tipoAtivo']),
            'message' => 'Item adicionado ao aditivo.',
        ], 201);
    }
}
