<?php

namespace App\Http\Controllers\Contrato\Aditivo;

use App\Actions\Contrato\Aditivo\Criar;
use App\Enums\TipoAditivo;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    /**
     * Cria um novo aditivo para o contrato.
     */
    public function __invoke(Request $request, string $contratoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $validated = $request->validate([
            'tipo' => ['required', Rule::enum(TipoAditivo::class)],
            'data_vigencia' => ['required', 'date'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'nova_data_termino' => ['nullable', 'date', 'after:today'],
            'valor_ajuste' => ['nullable', 'numeric'],
            'conceder_reembolso' => ['nullable', 'boolean'],
        ]);

        $criar = new Criar(
            contrato: $contrato,
            tipo: TipoAditivo::from($validated['tipo']),
            dataVigencia: new \DateTime($validated['data_vigencia']),
            descricao: $validated['descricao'] ?? null,
            novaDataTermino: isset($validated['nova_data_termino'])
                ? new \DateTime($validated['nova_data_termino'])
                : null,
            valorAjuste: $validated['valor_ajuste'] ?? null,
            concederReembolso: $validated['conceder_reembolso'] ?? false,
        );
        $criar->handle();
        $aditivo = $criar->getAditivo();

        return response()->json([
            'data' => $aditivo->load(['itens.tipoAtivo']),
            'message' => 'Aditivo criado com sucesso.',
        ], 201);
    }
}
