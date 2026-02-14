<?php

namespace App\Actions\Contrato\Aditivo\Stripe;

use App\Contracts\Command;
use App\Models\Contrato;
use App\Models\ContratoAditivo;
use Stripe\StripeClient;

/**
 * Atualiza a subscription Stripe com novo preço após alteração no contrato.
 */
class AtualizarSubscription implements Command
{
    private ?string $newPriceId = null;

    public function __construct(
        private Contrato $contrato,
        private ContratoAditivo $aditivo,
        private float $novoValorMensal
    ) {}

    /**
     * Executa a atualização da subscription.
     */
    public function handle(): void
    {
        // Verifica se contrato tem subscription
        if (! $this->contrato->stripe_subscription_id) {
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

        // Guarda price anterior para reversão
        $subscriptionItem = $subscription->items->data[0] ?? null;
        if ($subscriptionItem) {
            $this->aditivo->stripe_price_anterior_id = $subscriptionItem->price->id;
            $this->aditivo->save();
        }

        // Cria novo preço
        $newPrice = $stripe->prices->create([
            'product' => $subscriptionItem->price->product,
            'unit_amount' => (int) ($this->novoValorMensal * 100),
            'currency' => 'brl',
            'recurring' => ['interval' => 'month'],
        ], ['stripe_account' => $connectConfig->accountId]);

        $this->newPriceId = $newPrice->id;

        // Atualiza subscription sem proration (já calculamos manualmente)
        $stripe->subscriptions->update(
            $this->contrato->stripe_subscription_id,
            [
                'items' => [
                    ['id' => $subscriptionItem->id, 'price' => $newPrice->id],
                ],
                'proration_behavior' => 'none',
            ],
            ['stripe_account' => $connectConfig->accountId]
        );
    }

    public function getNewPriceId(): ?string
    {
        return $this->newPriceId;
    }
}
