<?php

namespace App\Http\Controllers\Stripe\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Status extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $connectConfig = $request->user()->locador()->stripeConnect();

        return response()->json([
            'data' => [
                'has_account' => $connectConfig->hasAccount(),
                'account_id' => $connectConfig->accountId,
                'onboarding_complete' => $connectConfig->onboardingComplete,
                'charges_enabled' => $connectConfig->chargesEnabled,
                'payouts_enabled' => $connectConfig->payoutsEnabled,
                'has_webhook' => $connectConfig->hasWebhook(),
            ],
        ]);
    }
}
