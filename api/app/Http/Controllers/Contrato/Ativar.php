<?php

namespace App\Http\Controllers\Contrato;

use App\Actions\Contrato\Ativar as AtivarAction;
use App\Exceptions\ContratoNaoPodeSerAtivadoException;
use App\Exceptions\ContratoSemItensException;
use App\Exceptions\QuantidadeIndisponivelException;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Ativar extends Controller
{
    /**
     * Ativa um contrato.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Contrato::with('itens.tipoAtivo');

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $contrato = $query->findOrFail($id);

        try {
            $ativar = new AtivarAction($contrato);
            $ativar->handle();

            return response()->json([
                'data' => $ativar->getContrato()->load(['locador', 'locatario', 'itens.tipoAtivo']),
                'message' => 'Contrato ativado com sucesso.',
            ]);
        } catch (ContratoNaoPodeSerAtivadoException|ContratoSemItensException|QuantidadeIndisponivelException $e) {
            return $e->render($request);
        }
    }
}
