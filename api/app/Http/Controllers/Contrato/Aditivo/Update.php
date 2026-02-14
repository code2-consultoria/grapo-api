<?php

namespace App\Http\Controllers\Contrato\Aditivo;

use App\Exceptions\AditivoImutavelException;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update extends Controller
{
    /**
     * Atualiza um aditivo em rascunho.
     */
    public function __invoke(Request $request, string $contratoId, string $aditivoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $aditivo = $contrato->aditivos()->findOrFail($aditivoId);

        if (! $aditivo->podeSerEditado()) {
            throw new AditivoImutavelException($aditivo->id, $aditivo->status);
        }

        $validated = $request->validate([
            'descricao' => ['nullable', 'string', 'max:2000'],
            'data_vigencia' => ['nullable', 'date'],
            'nova_data_termino' => ['nullable', 'date', 'after:today'],
            'valor_ajuste' => ['nullable', 'numeric'],
            'conceder_reembolso' => ['nullable', 'boolean'],
        ]);

        $aditivo->fill($validated);
        $aditivo->save();

        return response()->json([
            'data' => $aditivo->load(['itens.tipoAtivo']),
            'message' => 'Aditivo atualizado com sucesso.',
        ]);
    }
}
