<?php

namespace App\Http\Controllers\Contrato;

use App\Actions\Contrato\Atualizar;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Update extends Controller
{
    /**
     * Atualiza um contrato existente.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'locatario_id' => [
                'required',
                'uuid',
                Rule::exists('pessoas', 'id')->where(function ($query) use ($locador) {
                    $query->where('tipo', 'locatario');
                    if ($locador) {
                        $query->where('locador_id', $locador->id);
                    }
                }),
            ],
            'data_inicio' => ['required', 'date'],
            'data_termino' => ['required', 'date', 'after:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $locatario = Pessoa::findOrFail($validated['locatario_id']);

        try {
            $atualizar = new Atualizar(
                contrato: $contrato,
                locatario: $locatario,
                dataInicio: new \DateTime($validated['data_inicio']),
                dataTermino: new \DateTime($validated['data_termino']),
                observacoes: $validated['observacoes'] ?? null
            );
            $atualizar->handle();

            return response()->json([
                'data' => $contrato->fresh(['locador', 'locatario', 'itens.tipoAtivo']),
                'message' => 'Contrato atualizado com sucesso.',
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
