<?php

namespace App\Http\Controllers\Contrato\Aditivo;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Index extends Controller
{
    /**
     * Lista os aditivos de um contrato.
     */
    public function __invoke(Request $request, string $contratoId): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::where('locador_id', $locador->id)
            ->findOrFail($contratoId);

        $aditivos = $contrato->aditivos()
            ->with(['itens.tipoAtivo'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $aditivos,
        ]);
    }
}
