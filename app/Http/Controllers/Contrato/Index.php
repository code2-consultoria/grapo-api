<?php

namespace App\Http\Controllers\Contrato;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Lista contratos do locador.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Contrato::with(['locador', 'locatario', 'itens.tipoAtivo']);

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('locatario_id')) {
            $query->where('locatario_id', $request->input('locatario_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'ilike', "%{$search}%")
                    ->orWhereHas('locatario', function ($q2) use ($search) {
                        $q2->where('nome', 'ilike', "%{$search}%");
                    });
            });
        }

        $contratos = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'data' => $contratos->items(),
            'meta' => [
                'current_page' => $contratos->currentPage(),
                'last_page' => $contratos->lastPage(),
                'per_page' => $contratos->perPage(),
                'total' => $contratos->total(),
            ],
        ]);
    }
}
