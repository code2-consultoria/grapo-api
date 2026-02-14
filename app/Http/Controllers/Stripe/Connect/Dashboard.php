<?php

namespace App\Http\Controllers\Stripe\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class Dashboard extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $connectConfig = $request->user()->locador()->stripeConnect();

        if (! $connectConfig->hasAccount()) {
            return response()->json([
                'message' => 'Conta Stripe Connect não configurada.',
            ], 400);
        }

        $stripe = new StripeClient(config('cashier.secret'));

        $loginLink = $stripe->accounts->createLoginLink(
            $connectConfig->accountId
        );

        return response()->json([
            'dashboard_url' => $loginLink->url,
        ]);
    }
}
