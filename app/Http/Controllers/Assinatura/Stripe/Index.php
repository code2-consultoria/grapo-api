<?php

namespace App\Http\Controllers\Assinatura\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Resources\StripeSubscriptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class Index extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $locador = $request->user()->locador();

        $subscriptions = $locador->stripeSubscriptions()
            ->with('items')
            ->latest()
            ->get();

        return StripeSubscriptionResource::collection($subscriptions);
    }
}
