<?php

use App\Models\Contrato;
use App\Models\Pessoa;
use Stripe\Event;
use Stripe\Webhook;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'stripe_connect_config' => [
            'account_id' => 'acct_test123',
            'onboarding_complete' => true,
            'charges_enabled' => true,
            'payouts_enabled' => true,
        ],
    ]);
    $this->locatario = Pessoa::factory()->locatario()->create();
    $this->locatario->locador()->associate($this->locador);
    $this->locatario->save();

    $this->contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => 'ativo',
        'stripe_subscription_id' => 'sub_test123',
        'stripe_customer_id' => 'cus_test123',
        'dia_vencimento' => 10,
    ]);
});

// Validação de assinatura

test('retorna 400 para assinatura inválida', function () {
    $payload = json_encode(['type' => 'test.event']);

    $response = $this->postJson('/api/stripe/webhook', [], [
        'Stripe-Signature' => 'invalid_signature',
    ]);

    $response->assertStatus(400);
});

// Eventos de conta Connect

test('atualiza status da conta connect via webhook', function () {
    // Simula payload do evento account.updated
    $eventData = [
        'id' => 'evt_test123',
        'type' => 'account.updated',
        'data' => [
            'object' => [
                'id' => 'acct_test123',
                'details_submitted' => true,
                'charges_enabled' => true,
                'payouts_enabled' => true,
            ],
        ],
    ];

    // Mocka a verificação de assinatura do Stripe
    Webhook::shouldReceive('constructEvent')
        ->once()
        ->andReturn(new Event($eventData));

    $response = $this->postJson('/api/stripe/webhook', $eventData, [
        'Stripe-Signature' => 'valid_signature',
    ]);

    $response->assertStatus(200);

    // Verifica que o locador foi atualizado
    $this->locador->refresh();
    $config = $this->locador->stripeConnect();
    expect($config->onboardingComplete)->toBeTrue();
    expect($config->chargesEnabled)->toBeTrue();
    expect($config->payoutsEnabled)->toBeTrue();
})->skip('Requer mock do Stripe Webhook');

// Eventos de invoice

test('registra pagamento de invoice pago', function () {
    $eventData = [
        'id' => 'evt_test456',
        'type' => 'invoice.paid',
        'data' => [
            'object' => [
                'id' => 'inv_test123',
                'subscription' => 'sub_test123',
                'amount_paid' => 100000, // R$ 1.000,00
            ],
        ],
    ];

    Webhook::shouldReceive('constructEvent')
        ->once()
        ->andReturn(new Event($eventData));

    $response = $this->postJson('/api/stripe/webhook', $eventData, [
        'Stripe-Signature' => 'valid_signature',
    ]);

    $response->assertStatus(200);
})->skip('Requer mock do Stripe Webhook');

test('registra falha de pagamento de invoice', function () {
    $eventData = [
        'id' => 'evt_test789',
        'type' => 'invoice.payment_failed',
        'data' => [
            'object' => [
                'id' => 'inv_test456',
                'subscription' => 'sub_test123',
            ],
        ],
    ];

    Webhook::shouldReceive('constructEvent')
        ->once()
        ->andReturn(new Event($eventData));

    $response = $this->postJson('/api/stripe/webhook', $eventData, [
        'Stripe-Signature' => 'valid_signature',
    ]);

    $response->assertStatus(200);
})->skip('Requer mock do Stripe Webhook');

// Eventos de assinatura

test('remove dados stripe do contrato quando assinatura é cancelada', function () {
    $eventData = [
        'id' => 'evt_test_sub_deleted',
        'type' => 'customer.subscription.deleted',
        'data' => [
            'object' => [
                'id' => 'sub_test123',
            ],
        ],
    ];

    Webhook::shouldReceive('constructEvent')
        ->once()
        ->andReturn(new Event($eventData));

    $response = $this->postJson('/api/stripe/webhook', $eventData, [
        'Stripe-Signature' => 'valid_signature',
    ]);

    $response->assertStatus(200);

    // Verifica que os dados Stripe foram removidos
    $this->contrato->refresh();
    expect($this->contrato->stripe_subscription_id)->toBeNull();
    expect($this->contrato->stripe_customer_id)->toBeNull();
    expect($this->contrato->dia_vencimento)->toBeNull();
})->skip('Requer mock do Stripe Webhook');

// Eventos de checkout

test('processa checkout session completed', function () {
    $eventData = [
        'id' => 'evt_checkout_complete',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test123',
                'mode' => 'subscription',
                'subscription' => 'sub_new123',
                'customer' => 'cus_new123',
            ],
        ],
    ];

    Webhook::shouldReceive('constructEvent')
        ->once()
        ->andReturn(new Event($eventData));

    $response = $this->postJson('/api/stripe/webhook', $eventData, [
        'Stripe-Signature' => 'valid_signature',
    ]);

    $response->assertStatus(200);
})->skip('Requer mock do Stripe Webhook');

// Eventos não tratados

test('retorna ok para eventos não tratados', function () {
    $eventData = [
        'id' => 'evt_unknown',
        'type' => 'unknown.event.type',
        'data' => [
            'object' => [],
        ],
    ];

    Webhook::shouldReceive('constructEvent')
        ->once()
        ->andReturn(new Event($eventData));

    $response = $this->postJson('/api/stripe/webhook', $eventData, [
        'Stripe-Signature' => 'valid_signature',
    ]);

    $response->assertStatus(200);
})->skip('Requer mock do Stripe Webhook');
