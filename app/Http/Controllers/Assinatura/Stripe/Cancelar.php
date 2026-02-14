<?php

namespace App\Http\Controllers\Assinatura\Stripe;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Cancelar extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $locador = $request->user()->locador();
        $subscription = $locador->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return response()->json([
                'message' => 'Nenhuma assinatura ativa encontrada.',
            ], 404);
        }

        // Cancela no final do período atual
        $subscription->cancel();

        // Define data_limite_acesso para 30 dias (cancelamento)
        $locador->definirAcessoCancelamento();

        return response()->json([
            'message' => 'Assinatura cancelada. Acesso disponível até o fim do período.',
            'ends_at' => $subscription->ends_at,
            'data_limite_acesso' => $locador->data_limite_acesso->format('Y-m-d'),
        ]);
    }
}
