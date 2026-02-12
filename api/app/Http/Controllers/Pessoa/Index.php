<?php

namespace App\Http\Controllers\Pessoa;

use App\Enums\TipoPessoa;
use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Lista pessoas.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Pessoa::query();

        // Tipo pode vir da rota (defaults) ou query string
        $tipoParam = $request->route('tipo') ?? $request->input('tipo');

        if ($tipoParam) {
            $tipo = TipoPessoa::tryFrom($tipoParam);
            if ($tipo) {
                $query->porTipo($tipo);
            }
        }

        // Busca por nome
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nome', 'ilike', "%{$search}%");
        }

        // Filtro por ativo
        if ($request->has('ativo')) {
            $query->where('ativo', $request->boolean('ativo'));
        }

        // Se for cliente, filtra pelo locador
        if ($user->isCliente() && $locador) {
            // Se está buscando locadores, mostra apenas o próprio locador
            if ($tipoParam === 'locador') {
                $query->where('id', $locador->id);
            } else {
                // Para outros tipos, filtra pelo locador_id
                $query->where('locador_id', $locador->id);
            }
        }

        $pessoas = $query->with('documentos')->orderBy('nome')->paginate(15);

        return response()->json([
            'data' => $pessoas->items(),
            'meta' => [
                'current_page' => $pessoas->currentPage(),
                'last_page' => $pessoas->lastPage(),
                'per_page' => $pessoas->perPage(),
                'total' => $pessoas->total(),
            ],
        ]);
    }
}
