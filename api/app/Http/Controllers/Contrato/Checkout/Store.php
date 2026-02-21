<?php

namespace App\Http\Controllers\Contrato\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class Store extends Controller
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        // Validacoes
        if ($contrato->estaAtivo()) {
            return response()->json([
                'message' => 'Contrato ja esta ativo.',
            ], 400);
        }

        if (! $contrato->exigePagamentoAntecipado()) {
            return response()->json([
                'message' => 'Contrato nao exige pagamento antecipado.',
            ], 400);
        }

        $connectConfig = $contrato->locador->stripeConnect();
        if (! $connectConfig->isReady()) {
            return response()->json([
                'message' => 'Locador nao possui Stripe Connect configurado.',
            ], 400);
        }

        $validated = $request->validate([
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        $stripe = new StripeClient(config('cashier.secret'));

        // Define metodos de pagamento baseado no tipo
        $paymentMethods = ['card'];
        if ($contrato->tipo_cobranca->isPix()) {
            $paymentMethods = ['pix'];
        }

        // Cria checkout session
        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => $paymentMethods,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'brl',
                        'unit_amount' => (int) ($contrato->valor_total * 100),
                        'product_data' => [
                            'name' => "Contrato {$contrato->codigo}",
                            'description' => "Pagamento antecipado - {$contrato->locatario->nome}",
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'customer_email' => $contrato->locatario->email,
            'success_url' => $validated['success_url'].'?checkout_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $validated['cancel_url'],
            'metadata' => [
                'contrato_id' => $contrato->id,
                'tipo' => 'antecipado',
            ],
        ], [
            'stripe_account' => $connectConfig->accountId,
        ]);

        // Salva o checkout ID no contrato
        $contrato->update([
            'stripe_checkout_id' => $session->id,
        ]);

        return response()->json([
            'checkout_url' => $session->url,
            'checkout_id' => $session->id,
        ]);
    }
}
