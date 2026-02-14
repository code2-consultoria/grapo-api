<?php

namespace App\Http\Controllers\Contrato\Pagamento;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class Destroy extends Controller
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        if (! $contrato->stripe_subscription_id) {
            return response()->json([
                'message' => 'Contrato não possui pagamento Stripe configurado.',
            ], 400);
        }

        $connectConfig = $contrato->locador->stripeConnect();
        $stripe = new StripeClient(config('cashier.secret'));

        // Cancela a assinatura no Stripe
        $stripe->subscriptions->cancel(
            $contrato->stripe_subscription_id,
            [],
            ['stripe_account' => $connectConfig->accountId]
        );

        // Remove referências do contrato
        $contrato->update([
            'stripe_subscription_id' => null,
            'stripe_customer_id' => null,
            'dia_vencimento' => null,
        ]);

        return response()->json([
            'message' => 'Pagamento Stripe cancelado.',
        ]);
    }
}
