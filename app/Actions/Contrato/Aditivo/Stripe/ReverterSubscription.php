<?php

namespace App\Actions\Contrato\Aditivo\Stripe;

use App\Contracts\Command;
use App\Models\Contrato;
use App\Models\ContratoAditivo;
use Stripe\StripeClient;

/**
 * Reverte a subscription Stripe para o preço anterior após cancelamento de aditivo.
 */
class ReverterSubscription implements Command
{
    public function __construct(
        private Contrato $contrato,
        private ContratoAditivo $aditivo
    ) {}

    /**
     * Executa a reversão da subscription.
     */
    public function handle(): void
    {
        // Verifica se contrato tem subscription e aditivo tem price anterior
        if (! $this->contrato->stripe_subscription_id) {
            return;
        }

        if (! $this->aditivo->stripe_price_anterior_id) {
            return;
        }

        $locador = $this->contrato->locador;
        $connectConfig = $locador->stripeConnect();

        if (! $connectConfig->isReady()) {
            return;
        }

        $stripe = new StripeClient(config('cashier.secret'));

        // Recupera subscription atual
        $subscription = $stripe->subscriptions->retrieve(
            $this->contrato->stripe_subscription_id,
            [],
            ['stripe_account' => $connectConfig->accountId]
        );

        $subscriptionItem = $subscription->items->data[0] ?? null;
        if (! $subscriptionItem) {
            return;
        }

        // Reverte para o preço anterior
        $stripe->subscriptions->update(
            $this->contrato->stripe_subscription_id,
            [
                'items' => [
                    [
                        'id' => $subscriptionItem->id,
                        'price' => $this->aditivo->stripe_price_anterior_id,
                    ],
                ],
                'proration_behavior' => 'none',
            ],
            ['stripe_account' => $connectConfig->accountId]
        );

        // Remove invoice item se existir
        $this->cancelarInvoiceItem($stripe, $connectConfig->accountId);
    }

    /**
     * Cancela invoice item pendente se existir.
     */
    private function cancelarInvoiceItem(StripeClient $stripe, string $accountId): void
    {
        if (! $this->aditivo->stripe_invoice_item_id) {
            return;
        }

        try {
            $stripe->invoiceItems->delete(
                $this->aditivo->stripe_invoice_item_id,
                [],
                ['stripe_account' => $accountId]
            );
        } catch (\Exception $e) {
            // Invoice item já pode ter sido cobrado, ignora erro
        }
    }
}
