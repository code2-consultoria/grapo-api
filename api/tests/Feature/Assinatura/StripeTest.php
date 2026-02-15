<?php

use App\Models\Pessoa;
use App\Models\Plano;
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

test('cria checkout session para plano', function () {
    $plano = Plano::factory()->comStripe()->create();

    $response = $this->actingAs($this->user)
        ->postJson('/api/assinaturas/checkout', [
            'plano_id' => $plano->id,
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelar',
        ]);

    // Stripe retornará 500 em ambiente de teste sem mock real
    // Este teste verifica apenas a validação dos dados
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['plano_id'])
        ->unless(
            $response->status() === 422,
            fn ($r) => $r->assertJsonStructure(['checkout_url'])
        );
})->skip('Requer configuração de Stripe mock');

test('valida plano_id obrigatório no checkout', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/assinaturas/checkout', [
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelar',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['plano_id']);
});

test('valida plano existente no checkout', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/assinaturas/checkout', [
            'plano_id' => \Illuminate\Support\Str::uuid()->toString(),
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelar',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['plano_id']);
});

test('valida plano ativo no checkout', function () {
    $plano = Plano::factory()->comStripe()->create(['ativo' => false]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/assinaturas/checkout', [
            'plano_id' => $plano->id,
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelar',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['plano_id']);
});

test('valida plano com stripe_price_id no checkout', function () {
    $plano = Plano::factory()->create(['ativo' => true]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/assinaturas/checkout', [
            'plano_id' => $plano->id,
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelar',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['plano_id']);
});

test('valida success_url obrigatório no checkout', function () {
    $plano = Plano::factory()->comStripe()->create();

    $response = $this->actingAs($this->user)
        ->postJson('/api/assinaturas/checkout', [
            'plano_id' => $plano->id,
            'cancel_url' => 'http://localhost/cancelar',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['success_url']);
});

test('valida cancel_url obrigatório no checkout', function () {
    $plano = Plano::factory()->comStripe()->create();

    $response = $this->actingAs($this->user)
        ->postJson('/api/assinaturas/checkout', [
            'plano_id' => $plano->id,
            'success_url' => 'http://localhost/sucesso',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['cancel_url']);
});

test('requer autenticação para checkout', function () {
    $plano = Plano::factory()->comStripe()->create();

    $response = $this->postJson('/api/assinaturas/checkout', [
        'plano_id' => $plano->id,
        'success_url' => 'http://localhost/sucesso',
        'cancel_url' => 'http://localhost/cancelar',
    ]);

    $response->assertStatus(401);
});

test('lista assinaturas do locador', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/assinaturas');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('requer autenticação para listar assinaturas', function () {
    $response = $this->getJson('/api/assinaturas');

    $response->assertStatus(401);
});

test('exibe status da assinatura atual', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/assinaturas/status');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'has_subscription',
                'subscription',
            ],
        ]);
});

test('requer autenticação para ver status', function () {
    $response = $this->getJson('/api/assinaturas/status');

    $response->assertStatus(401);
});
