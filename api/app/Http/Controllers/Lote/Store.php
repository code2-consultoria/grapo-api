<?php

namespace App\Http\Controllers\Lote;

use App\Actions\Lote\Criar;
use App\Http\Controllers\Controller;
use App\Models\TipoAtivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    /**
     * Cria um novo lote.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $validated = $request->validate([
            'tipo_ativo_id' => [
                'required',
                'uuid',
                Rule::exists('tipos_ativos', 'id')->where(function ($query) use ($locador) {
                    return $query->where('locador_id', $locador->id);
                }),
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('lotes')->where(function ($query) use ($locador) {
                    return $query->where('locador_id', $locador->id);
                }),
            ],
            'quantidade_total' => ['required', 'integer', 'min:1'],
            'fornecedor' => ['nullable', 'string', 'max:255'],
            'valor_total' => ['nullable', 'numeric', 'min:0'],
            'valor_frete' => ['nullable', 'numeric', 'min:0'],
            'forma_pagamento' => ['nullable', 'string', 'max:100'],
            'nf' => ['nullable', 'string', 'max:100'],
            'data_aquisicao' => ['nullable', 'date'],
        ]);

        $tipoAtivo = TipoAtivo::findOrFail($validated['tipo_ativo_id']);

        $criar = new Criar(
            locador: $locador,
            tipoAtivo: $tipoAtivo,
            codigo: $validated['codigo'],
            quantidadeTotal: $validated['quantidade_total'],
            fornecedor: $validated['fornecedor'] ?? null,
            valorTotal: $validated['valor_total'] ?? null,
            valorFrete: $validated['valor_frete'] ?? null,
            formaPagamento: $validated['forma_pagamento'] ?? null,
            nf: $validated['nf'] ?? null,
            dataAquisicao: isset($validated['data_aquisicao'])
                ? new \DateTime($validated['data_aquisicao'])
                : null
        );
        $criar->handle();

        return response()->json([
            'data' => $criar->getLote()->load('tipoAtivo'),
            'message' => 'Lote criado com sucesso.',
        ], 201);
    }
}
