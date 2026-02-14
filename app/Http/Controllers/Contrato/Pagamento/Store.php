<?php

namespace App\Http\Controllers\Contrato\Pagamento;

use App\Enums\StatusContrato;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class Store extends Controller
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'dia_vencimento' => 'required|integer|min:1|max:28',
        ]);

        $contrato = Contrato::findOrFail($id);
        $locador = $contrato->locador;
        $locatario = $contrato->locatario;

        $connectConfig = $locador->stripeConnect();

        // Validações
        if (! $connectConfig->isReady()) {
            return response()->json([
                'message' => 'Locador não possui Stripe Connect configurado.',
            ], 400);
        }

        if ($contrato->status !== StatusContrato::Ativo) {
            return response()->json([
                'message' => 'Contrato não está ativo.',
            ], 400);
        }

        if (! $locatario->email) {
            return response()->json([
                'message' => 'Locatário não possui email cadastrado.',
            ], 400);
        }

        $stripe = new StripeClient(config('cashier.secret'));

        // Cria ou recupera customer do locatário na conta Connect do locador
        $customerId = $contrato->stripe_customer_id;
        if (! $customerId) {
            $customer = $stripe->customers->create([
                'email' => $locatario->email,
                'name' => $locatario->nome,
                'metadata' => [
                    'locatario_id' => $locatario->id,
                    'contrato_id' => $contrato->id,
                ],
            ], [
                'stripe_account' => $connectConfig->accountId,
            ]);
            $customerId = $customer->id;
        }

        // Calcula valor mensal do contrato
        $valorMensal = $contrato->itens->sum(function ($item) {
            return $item->valor_mensal * $item->quantidade;
        });

        // Cria produto e preço na conta Connect
        $product = $stripe->products->create([
            'name' => "Aluguel - Contrato #{$contrato->id}",
            'metadata' => [
                'contrato_id' => $contrato->id,
            ],
        ], [
            'stripe_account' => $locador->stripe_account_id,
        ]);

        $price = $stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => (int) ($valorMensal * 100), // Centavos
            'currency' => 'brl',
            'recurring' => [
                'interval' => 'month',
            ],
        ], [
            'stripe_account' => $locador->stripe_account_id,
        ]);

        // Cria a assinatura
        $subscription = $stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => [
                ['price' => $price->id],
            ],
            'billing_cycle_anchor' => $this->calcularProximoVencimento($validated['dia_vencimento']),
            'proration_behavior' => 'none',
            'metadata' => [
                'contrato_id' => $contrato->id,
            ],
        ], [
            'stripe_account' => $locador->stripe_account_id,
        ]);

        // Atualiza o contrato
        $contrato->update([
            'stripe_subscription_id' => $subscription->id,
            'stripe_customer_id' => $customerId,
            'dia_vencimento' => $validated['dia_vencimento'],
        ]);

        return response()->json([
            'data' => [
                'stripe_subscription_id' => $subscription->id,
                'dia_vencimento' => $validated['dia_vencimento'],
                'valor_mensal' => $valorMensal,
            ],
        ], 201);
    }

    private function calcularProximoVencimento(int $dia): int
    {
        $hoje = now();
        $vencimento = $hoje->copy()->day($dia);

        // Se o dia já passou neste mês, vai para o próximo
        if ($vencimento->lte($hoje)) {
            $vencimento->addMonth();
        }

        return $vencimento->timestamp;
    }
}
