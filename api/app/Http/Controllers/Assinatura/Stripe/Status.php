<?php

namespace App\Http\Controllers\Assinatura\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Resources\StripeSubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Status extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $locador = $request->user()->locador();

        $subscription = $locador->subscription('default');
        $hasSubscription = $subscription !== null && $subscription->active();

        return response()->json([
            'data' => [
                'has_subscription' => $hasSubscription,
                'data_limite_acesso' => $locador->data_limite_acesso?->format('Y-m-d'),
                'has_acesso_ativo' => $locador->hasAcessoAtivo(),
                'subscription' => $hasSubscription
                    ? new StripeSubscriptionResource($subscription)
                    : null,
            ],
        ]);
    }
}
