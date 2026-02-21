<?php

namespace App\Http\Controllers\Assinatura\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Plano;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Checkout extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plano_id' => [
                'required',
                Rule::exists('planos', 'id')
                    ->where('ativo', true)
                    ->whereNotNull('stripe_price_id'),
            ],
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        $plano = Plano::find($validated['plano_id']);
        $locador = $request->user()->locador();

        // Cria a sessão de checkout do Stripe
        $checkout = $locador->newSubscription('default', $plano->stripe_price_id)
            ->checkout([
                'success_url' => $validated['success_url'].'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $validated['cancel_url'],
            ]);

        return response()->json([
            'checkout_url' => $checkout->url,
            'session_id' => $checkout->id,
        ]);
    }
}
