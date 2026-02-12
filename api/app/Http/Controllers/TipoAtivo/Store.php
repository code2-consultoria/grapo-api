<?php

namespace App\Http\Controllers\TipoAtivo;

use App\Actions\TipoAtivo\Criar;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    /**
     * Cria um novo tipo de ativo.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_ativos')->where(function ($query) use ($locador) {
                    return $query->where('locador_id', $locador->id);
                }),
            ],
            'descricao' => ['nullable', 'string', 'max:1000'],
            'unidade_medida' => ['nullable', 'string', 'max:50'],
            'valor_diaria_sugerido' => ['nullable', 'numeric', 'min:0'],
        ]);

        $criar = new Criar(
            locador: $locador,
            nome: $validated['nome'],
            descricao: $validated['descricao'] ?? null,
            unidadeMedida: $validated['unidade_medida'] ?? 'unidade',
            valorDiariaSugerido: $validated['valor_diaria_sugerido'] ?? null
        );
        $criar->handle();

        return response()->json([
            'data' => $criar->getTipoAtivo(),
            'message' => 'Tipo de ativo criado com sucesso.',
        ], 201);
    }
}
