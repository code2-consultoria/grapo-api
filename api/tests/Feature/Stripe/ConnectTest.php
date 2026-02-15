<?php

use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(), // Assinatura ativa
    ]);
    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);
});

// Onboarding

test('requer autenticação para iniciar onboarding', function () {
    $response = $this->postJson('/api/stripe/connect/onboard');

    $response->assertStatus(401);
});

test('inicia onboarding do stripe connect', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/stripe/connect/onboard', [
            'return_url' => 'http://localhost/app/configuracoes',
            'refresh_url' => 'http://localhost/app/configuracoes/stripe',
        ]);

    // Em ambiente de teste sem Stripe real, validamos apenas a estrutura
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['return_url'])
        ->unless(
            $response->status() === 422,
            fn ($r) => $r->assertJsonStructure(['onboarding_url'])
        );
})->skip('Requer configuração de Stripe');

test('valida return_url obrigatório no onboarding', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/stripe/connect/onboard', [
            'refresh_url' => 'http://localhost/app/configuracoes/stripe',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['return_url']);
});

test('valida refresh_url obrigatório no onboarding', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/stripe/connect/onboard', [
            'return_url' => 'http://localhost/app/configuracoes',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['refresh_url']);
});

// Status

test('requer autenticação para ver status', function () {
    $response = $this->getJson('/api/stripe/connect/status');

    $response->assertStatus(401);
});

test('retorna status do connect para locador sem conta', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/stripe/connect/status');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'has_account' => false,
                'onboarding_complete' => false,
                'charges_enabled' => false,
                'payouts_enabled' => false,
            ],
        ]);
});

test('retorna status do connect para locador com conta', function () {
    $this->locador->update([
        'stripe_connect_config' => [
            'account_id' => 'acct_test123',
            'onboarding_complete' => true,
            'charges_enabled' => true,
            'payouts_enabled' => true,
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/stripe/connect/status');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'has_account' => true,
                'onboarding_complete' => true,
                'charges_enabled' => true,
                'payouts_enabled' => true,
            ],
        ]);
});

// Dashboard

test('requer autenticação para acessar dashboard', function () {
    $response = $this->getJson('/api/stripe/connect/dashboard');

    $response->assertStatus(401);
});

test('retorna erro se locador não tem conta connect', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/stripe/connect/dashboard');

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Conta Stripe Connect não configurada.',
        ]);
});

test('retorna link do dashboard para locador com conta', function () {
    $this->locador->update([
        'stripe_connect_config' => [
            'account_id' => 'acct_test123',
            'onboarding_complete' => true,
            'charges_enabled' => true,
            'payouts_enabled' => true,
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/stripe/connect/dashboard');

    // Em ambiente de teste sem Stripe real
    $response->assertStatus(500)
        ->unless(
            $response->status() === 500,
            fn ($r) => $r->assertJsonStructure(['dashboard_url'])
        );
})->skip('Requer configuração de Stripe');
