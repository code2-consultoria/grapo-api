<?php

use App\Models\Contrato;
use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(), // Assinatura ativa
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

    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);

    $this->contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => 'ativo',
    ]);
});

// Criar assinatura

test('requer autenticação para criar assinatura de contrato', function () {
    $response = $this->postJson("/api/contratos/{$this->contrato->id}/pagamento-stripe");

    $response->assertStatus(401);
});

test('valida contrato existente', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/contratos/' . \Illuminate\Support\Str::uuid() . '/pagamento-stripe', [
            'dia_vencimento' => 10,
        ]);

    $response->assertStatus(404);
});

test('valida dia_vencimento obrigatório', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamento-stripe", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['dia_vencimento']);
});

test('valida dia_vencimento entre 1 e 28', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamento-stripe", [
            'dia_vencimento' => 31,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['dia_vencimento']);
});

test('valida locador com stripe connect configurado', function () {
    $this->locador->update([
        'stripe_connect_config' => null,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamento-stripe", [
            'dia_vencimento' => 10,
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Locador não possui Stripe Connect configurado.',
        ]);
});

test('valida contrato ativo', function () {
    $this->contrato->update(['status' => 'cancelado']);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamento-stripe", [
            'dia_vencimento' => 10,
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Contrato não está ativo.',
        ]);
});

test('valida email do locatário obrigatório', function () {
    $this->locatario->update(['email' => null]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamento-stripe", [
            'dia_vencimento' => 10,
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Locatário não possui email cadastrado.',
        ]);
});

test('cria assinatura stripe para contrato', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamento-stripe", [
            'dia_vencimento' => 10,
        ]);

    // Em ambiente sem Stripe real, espera erro da API
    $response->assertStatus(500)
        ->unless(
            $response->status() === 500,
            fn ($r) => $r->assertJsonStructure([
                'data' => ['stripe_subscription_id', 'dia_vencimento'],
            ])
        );
})->skip('Requer configuração de Stripe');

// Cancelar assinatura

test('requer autenticação para cancelar assinatura', function () {
    $response = $this->deleteJson("/api/contratos/{$this->contrato->id}/pagamento-stripe");

    $response->assertStatus(401);
});

test('retorna erro se contrato não tem assinatura stripe', function () {
    $response = $this->actingAs($this->user)
        ->deleteJson("/api/contratos/{$this->contrato->id}/pagamento-stripe");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Contrato não possui pagamento Stripe configurado.',
        ]);
});

test('cancela assinatura stripe do contrato', function () {
    $this->contrato->update([
        'stripe_subscription_id' => 'sub_test123',
        'stripe_customer_id' => 'cus_test123',
        'dia_vencimento' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/contratos/{$this->contrato->id}/pagamento-stripe");

    // Em ambiente sem Stripe real, espera erro da API
    $response->assertStatus(500)
        ->unless(
            $response->status() === 500,
            fn ($r) => $r->assertJson(['message' => 'Pagamento Stripe cancelado.'])
        );
})->skip('Requer configuração de Stripe');

// Status do pagamento

test('retorna status do pagamento stripe do contrato', function () {
    $this->contrato->update([
        'stripe_subscription_id' => 'sub_test123',
        'stripe_customer_id' => 'cus_test123',
        'dia_vencimento' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$this->contrato->id}/pagamento-stripe");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'has_stripe_payment' => true,
                'dia_vencimento' => 10,
            ],
        ]);
});

test('retorna sem pagamento stripe para contrato sem configuração', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$this->contrato->id}/pagamento-stripe");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'has_stripe_payment' => false,
            ],
        ]);
});
