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
            'fornecedor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'valor_total' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'valor_frete' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'forma_pagamento' => ['sometimes', 'nullable', 'string', 'max:100'],
            'nf' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:disponivel,indisponivel,baixado'],
        ]);

        // Recalcula custo de aquisição se valor_total ou valor_frete foram alterados
        if (isset($validated['valor_total']) || isset($validated['valor_frete'])) {
            $valorTotal = $validated['valor_total'] ?? $lote->valor_total ?? 0;
            $valorFrete = $validated['valor_frete'] ?? $lote->valor_frete ?? 0;
            $validated['custo_aquisicao'] = (float) $valorTotal + (float) $valorFrete;
        }

        $atualizar = new Atualizar($lote, $validated);
        $atualizar->handle();

        return response()->json([
            'data' => $atualizar->getLote()->load('tipoAtivo'),
            'message' => 'Lote atualizado com sucesso.',
        ]);
    }
}
