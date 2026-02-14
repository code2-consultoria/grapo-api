<?php

namespace App\Http\Controllers\Stripe\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class Onboard extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'return_url' => 'required|url',
            'refresh_url' => 'required|url',
        ]);

        $locador = $request->user()->locador();
        $stripe = new StripeClient(config('cashier.secret'));
        $connectConfig = $locador->stripeConnect();

        // Cria ou recupera a conta Connect
        if (! $connectConfig->hasAccount()) {
            $account = $stripe->accounts->create([
                'type' => 'express',
                'country' => 'BR',
                'email' => $locador->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => $locador->isPessoaJuridica() ? 'company' : 'individual',
            ]);

            $connectConfig = $connectConfig->withAccount($account->id);
            $locador->updateStripeConnect($connectConfig);
        }

        // Cria link de onboarding
        $accountLink = $stripe->accountLinks->create([
            'account' => $connectConfig->accountId,
            'refresh_url' => $validated['refresh_url'],
            'return_url' => $validated['return_url'],
            'type' => 'account_onboarding',
        ]);

        return response()->json([
            'onboarding_url' => $accountLink->url,
        ]);
    }
}
