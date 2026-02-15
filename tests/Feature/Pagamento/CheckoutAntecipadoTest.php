<?php

use App\Enums\StatusContrato;
use App\Enums\TipoCobranca;
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
    $this->locatario = Pessoa::factory()->locatario()->create([
        'email' => 'locatario@teste.com',
    ]);
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
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::SemCobranca,
        'valor_total' => 1500.00,
    ]);
});

// Definir tipo de cobranca

test('define tipo de cobranca do contrato', function () {
    $response = $this->actingAs($this->user)
        ->putJson("/api/contratos/{$this->contrato->id}/tipo-cobranca", [
            'tipo_cobranca' => 'antecipado_stripe',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'tipo_cobranca' => 'antecipado_stripe',
            ],
        ]);

    $this->contrato->refresh();
    expect($this->contrato->tipo_cobranca)->toBe(TipoCobranca::AntecipadoStripe);
});

test('valida tipo de cobranca invalido', function () {
    $response = $this->actingAs($this->user)
        ->putJson("/api/contratos/{$this->contrato->id}/tipo-cobranca", [
            'tipo_cobranca' => 'invalido',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tipo_cobranca']);
});

test('nao permite alterar tipo de cobranca de contrato ativo', function () {
    $this->contrato->update(['status' => StatusContrato::Ativo]);

    $response = $this->actingAs($this->user)
        ->putJson("/api/contratos/{$this->contrato->id}/tipo-cobranca", [
            'tipo_cobranca' => 'antecipado_stripe',
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Tipo de cobranca nao pode ser alterado para contratos ativos.',
        ]);
});

// Checkout antecipado

test('cria checkout para pagamento antecipado com cartao', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::AntecipadoStripe,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/checkout", [
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelado',
        ]);

    // Em ambiente sem Stripe real, espera erro da API
    $response->assertStatus(500)
        ->unless(
            $response->status() === 500,
            fn ($r) => $r->assertJsonStructure(['checkout_url'])
        );
})->skip('Requer configuracao de Stripe');

test('cria checkout para pagamento antecipado com pix', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::AntecipadoPix,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/checkout", [
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelado',
        ]);

    // Em ambiente sem Stripe real, espera erro da API
    $response->assertStatus(500)
        ->unless(
            $response->status() === 500,
            fn ($r) => $r->assertJsonStructure(['checkout_url'])
        );
})->skip('Requer configuracao de Stripe');

test('nao permite checkout para contrato sem cobranca antecipada', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::RecorrenteStripe,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/checkout", [
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelado',
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Contrato nao exige pagamento antecipado.',
        ]);
});

test('nao permite checkout para contrato ja ativo', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::AntecipadoStripe,
        'status' => StatusContrato::Ativo,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/checkout", [
            'success_url' => 'http://localhost/sucesso',
            'cancel_url' => 'http://localhost/cancelado',
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Contrato ja esta ativo.',
        ]);
});

test('valida urls obrigatorias no checkout', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::AntecipadoStripe,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/checkout", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['success_url', 'cancel_url']);
});

// Ativacao por pagamento

test('contrato com pagamento antecipado vai para aguardando pagamento', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::AntecipadoStripe,
    ]);

    // Tenta ativar o contrato
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/ativar");

    // Deve ir para aguardando pagamento, nao ativo
    $response->assertStatus(200);

    $this->contrato->refresh();
    expect($this->contrato->status)->toBe(StatusContrato::AguardandoPagamento);
});

test('contrato sem cobranca vai direto para ativo', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::SemCobranca,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/ativar");

    $response->assertStatus(200);

    $this->contrato->refresh();
    expect($this->contrato->status)->toBe(StatusContrato::Ativo);
});

test('contrato com cobranca recorrente vai direto para ativo', function () {
    $this->contrato->update([
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/ativar");

    $response->assertStatus(200);

    $this->contrato->refresh();
    expect($this->contrato->status)->toBe(StatusContrato::Ativo);
});
