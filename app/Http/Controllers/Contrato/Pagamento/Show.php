<?php

namespace App\Http\Controllers\Contrato\Pagamento;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $hasStripePayment = $contrato->stripe_subscription_id !== null;

        return response()->json([
            'data' => [
                'has_stripe_payment' => $hasStripePayment,
                'stripe_subscription_id' => $contrato->stripe_subscription_id,
                'stripe_customer_id' => $contrato->stripe_customer_id,
                'dia_vencimento' => $contrato->dia_vencimento,
            ],
        ]);
    }
}
