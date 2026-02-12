<?php

namespace App\Http\Controllers\Contrato\Item;

use App\Actions\Contrato\Item\Adicionar;
use App\Exceptions\ContratoAtivoImutavelException;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\TipoAtivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    /**
     * Adiciona um item ao contrato.
     */
    public function __invoke(Request $request, string $contratoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Contrato::query();

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $contrato = $query->findOrFail($contratoId);

        $validated = $request->validate([
            'tipo_ativo_id' => [
                'required',
                'uuid',
                Rule::exists('tipos_ativos', 'id')->where(function ($query) use ($locador) {
                    return $query->where('locador_id', $locador->id);
                }),
            ],
            'quantidade' => ['required', 'integer', 'min:1'],
            'valor_unitario_diaria' => ['required', 'numeric', 'min:0'],
        ]);

        $tipoAtivo = TipoAtivo::findOrFail($validated['tipo_ativo_id']);

        try {
            $adicionar = new Adicionar(
                contrato: $contrato,
                tipoAtivo: $tipoAtivo,
                quantidade: $validated['quantidade'],
                valorUnitarioDiaria: $validated['valor_unitario_diaria']
            );
            $adicionar->handle();

            return response()->json([
                'data' => $adicionar->getItem()->load('tipoAtivo'),
                'message' => 'Item adicionado ao contrato.',
            ], 201);
        } catch (ContratoAtivoImutavelException $e) {
            return $e->render($request);
        }
    }
}
