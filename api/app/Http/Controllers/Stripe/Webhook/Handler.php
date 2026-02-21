<?php

namespace App\Http\Controllers\Stripe\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class Handler extends Controller
{
    /**
     * Processa webhooks do Stripe.
     * Suporta tanto webhooks da plataforma quanto webhooks de contas Connect.
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Tenta validar com o webhook secret da plataforma
        $webhookSecret = config('cashier.webhook.secret');
        $event = null;

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        // Processa o evento
        $this->handleEvent($event);

        return response('OK', 200);
    }

    protected function handleEvent(\Stripe\Event $event): void
    {
        $method = 'handle'.str_replace('.', '', ucwords($event->type, '.'));

        if (method_exists($this, $method)) {
            $this->{$method}($event);
        } else {
            Log::info("Stripe webhook event not handled: {$event->type}");
        }
    }

    /**
     * Quando uma conta Connect completa o onboarding ou atualiza.
     */
    protected function handleAccountUpdated(\Stripe\Event $event): void
    {
        $account = $event->data->object;

        $locador = Pessoa::whereRaw(
            "stripe_connect_config->>'account_id' = ?",
            [$account->id]
        )->first();

        if (! $locador) {
            Log::warning("Account not found for Stripe account: {$account->id}");

            return;
        }

        $connectConfig = $locador->stripeConnect();
        $connectConfig = $connectConfig->withStatus(
            onboardingComplete: $account->details_submitted,
            chargesEnabled: $account->charges_enabled,
            payoutsEnabled: $account->payouts_enabled,
        );

        $locador->updateStripeConnect($connectConfig);

        Log::info("Stripe Connect account updated: {$account->id}", [
            'locador_id' => $locador->id,
            'charges_enabled' => $account->charges_enabled,
        ]);
    }

    /**
     * Quando um pagamento de assinatura é realizado com sucesso.
     */
    protected function handleInvoicePaid(\Stripe\Event $event): void
    {
        $invoice = $event->data->object;
        $subscriptionId = $invoice->subscription;
        $customerId = $invoice->customer;

        if (! $subscriptionId) {
            return;
        }

        // Verifica se é uma assinatura de plano da plataforma (locador)
        $locador = Pessoa::where('stripe_id', $customerId)->first();

        if ($locador) {
            // Atualiza data_limite_acesso para 60 dias
            $locador->atualizarAcessoPorPagamento();

            Log::info("Platform subscription paid for locador: {$locador->id}", [
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_paid / 100,
            ]);

            return;
        }

        // Busca pelo ID da assinatura no Stripe (contratos)
        $contrato = Contrato::where('stripe_subscription_id', $subscriptionId)->first();

        if ($contrato) {
            Log::info("Invoice paid for contract: {$contrato->id}", [
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_paid / 100,
            ]);

            // TODO: Registrar pagamento no histórico do contrato
        }
    }

    /**
     * Quando um pagamento de assinatura falha.
     */
    protected function handleInvoicePaymentFailed(\Stripe\Event $event): void
    {
        $invoice = $event->data->object;
        $subscriptionId = $invoice->subscription;

        if (! $subscriptionId) {
            return;
        }

        $contrato = Contrato::where('stripe_subscription_id', $subscriptionId)->first();

        if ($contrato) {
            Log::warning("Invoice payment failed for contract: {$contrato->id}", [
                'invoice_id' => $invoice->id,
            ]);

            // TODO: Notificar locador e locatário sobre falha no pagamento
        }
    }

    /**
     * Quando uma assinatura é cancelada.
     */
    protected function handleCustomerSubscriptionDeleted(\Stripe\Event $event): void
    {
        $subscription = $event->data->object;

        $contrato = Contrato::where('stripe_subscription_id', $subscription->id)->first();

        if ($contrato) {
            $contrato->update([
                'stripe_subscription_id' => null,
                'stripe_customer_id' => null,
                'dia_vencimento' => null,
            ]);

            Log::info("Subscription deleted for contract: {$contrato->id}");
        }
    }

    /**
     * Quando um checkout de assinatura da plataforma é concluído.
     */
    protected function handleCheckoutSessionCompleted(\Stripe\Event $event): void
    {
        $session = $event->data->object;

        // Verifica se é uma assinatura
        if ($session->mode !== 'subscription') {
            return;
        }

        // O subscription ID está disponível na sessão
        $subscriptionId = $session->subscription;
        $customerId = $session->customer;

        Log::info('Checkout session completed', [
            'subscription_id' => $subscriptionId,
            'customer_id' => $customerId,
        ]);

        // TODO: Ativar assinatura da plataforma se necessário
    }
}
