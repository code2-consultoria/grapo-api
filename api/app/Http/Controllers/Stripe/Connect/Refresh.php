<?php

namespace App\Http\Controllers\Stripe\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class Refresh extends Controller
{
    /**
     * Atualiza o status da conta Connect consultando a API do Stripe.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $locador = $request->user()->locador();
        $connectConfig = $locador->stripeConnect();

        if (! $connectConfig->hasAccount()) {
            return response()->json([
                'message' => 'Conta Stripe Connect não configurada.',
            ], 400);
        }

        $stripe = new StripeClient(config('cashier.secret'));
        $account = $stripe->accounts->retrieve($connectConfig->accountId);

        $connectConfig = $connectConfig->withStatus(
            onboardingComplete: $account->details_submitted,
            chargesEnabled: $account->charges_enabled,
            payoutsEnabled: $account->payouts_enabled,
        );
        $locador->updateStripeConnect($connectConfig);

        // Verifica pendencias
        $requirements = $account->requirements;
        $hasPendingRequirements = ! empty($requirements->currently_due) || ! empty($requirements->past_due);

        // Gera link para resolver pendencias se necessario
        $onboardingUrl = null;
        if ($hasPendingRequirements || ! $account->charges_enabled) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:5180');
            $returnUrl = $request->input('return_url', $frontendUrl . '/app/perfil');
            $refreshUrl = $request->input('refresh_url', $returnUrl . '?refresh=true');

            $accountLink = $stripe->accountLinks->create([
                'account' => $connectConfig->accountId,
                'refresh_url' => $refreshUrl,
                'return_url' => $returnUrl,
                'type' => 'account_onboarding',
            ]);
            $onboardingUrl = $accountLink->url;
        }

        return response()->json([
            'data' => [
                'has_account' => true,
                'account_id' => $connectConfig->accountId,
                'onboarding_complete' => $connectConfig->onboardingComplete,
                'charges_enabled' => $connectConfig->chargesEnabled,
                'payouts_enabled' => $connectConfig->payoutsEnabled,
                'disabled_reason' => $requirements->disabled_reason,
                'pending_requirements' => array_merge(
                    $requirements->currently_due ?? [],
                    $requirements->past_due ?? []
                ),
                'requirements_errors' => collect($requirements->errors ?? [])->map(fn ($e) => [
                    'code' => $e->code,
                    'reason' => $e->reason,
                ])->toArray(),
                'onboarding_url' => $onboardingUrl,
            ],
        ]);
    }
}
