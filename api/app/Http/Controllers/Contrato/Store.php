<?php

namespace App\Http\Controllers\Contrato;

use App\Actions\Contrato\Criar;
use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    /**
     * Cria um novo contrato.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $validated = $request->validate([
            'locatario_id' => [
                'required',
                'uuid',
                Rule::exists('pessoas', 'id')->where(function ($query) use ($locador) {
                    $query->where('tipo', 'locatario');
                    // Verifica se o locatário pertence ao locador do usuário
                    if ($locador) {
                        $query->where('locador_id', $locador->id);
                    }
                }),
            ],
            'data_inicio' => ['required', 'date', 'after_or_equal:today'],
            'data_termino' => ['required', 'date', 'after:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $locatario = Pessoa::findOrFail($validated['locatario_id']);

        $criar = new Criar(
            locador: $locador,
            locatario: $locatario,
            dataInicio: new \DateTime($validated['data_inicio']),
            dataTermino: new \DateTime($validated['data_termino']),
            observacoes: $validated['observacoes'] ?? null
        );
        $criar->handle();
        $contrato = $criar->getContrato();

        return response()->json([
            'data' => $contrato->load(['locador', 'locatario', 'itens']),
            'message' => 'Contrato criado com sucesso.',
        ], 201);
    }
}
