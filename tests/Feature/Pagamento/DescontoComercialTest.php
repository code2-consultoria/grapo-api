<?php

use App\Enums\StatusContrato;
use App\Enums\StatusPagamento;
use App\Enums\TipoCobranca;
use App\Models\Contrato;
use App\Models\Pagamento;
use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create();
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
        'status' => StatusContrato::Ativo,
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
        'valor_total' => 1500.00,
    ]);
});

// Criar pagamento com desconto

test('registra pagamento com desconto comercial', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'desconto_comercial' => 150.00,
            'data_vencimento' => '2026-02-15',
            'origem' => 'manual',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'valor' => '1500.00',
                'desconto_comercial' => '150.00',
                'valor_final' => 1350.00,
                'status' => 'pendente',
            ],
        ]);
});

test('registra pagamento sem desconto comercial', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'data_vencimento' => '2026-02-15',
            'origem' => 'manual',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'valor' => '1500.00',
                'desconto_comercial' => '0.00',
                'valor_final' => 1500.00,
            ],
        ]);
});

test('registra pagamento com desconto igual ao valor (100% desconto)', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'desconto_comercial' => 1500.00,
            'data_vencimento' => '2026-02-15',
            'origem' => 'manual',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'valor' => '1500.00',
                'desconto_comercial' => '1500.00',
                'valor_final' => 0.00,
            ],
        ]);
});

// Validacoes

test('nao permite desconto maior que valor da parcela', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'desconto_comercial' => 1500.01,
            'data_vencimento' => '2026-02-15',
            'origem' => 'manual',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['desconto_comercial']);
});

test('nao permite desconto negativo', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'desconto_comercial' => -10.00,
            'data_vencimento' => '2026-02-15',
            'origem' => 'manual',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['desconto_comercial']);
});

// Model accessor

test('valor_final e calculado corretamente no model', function () {
    $pagamento = Pagamento::factory()->create([
        'contrato_id' => $this->contrato->id,
        'valor' => 1000.00,
        'desconto_comercial' => 100.00,
    ]);

    expect($pagamento->valor_final)->toBe(900.00);
});

test('valor_final e igual ao valor quando nao ha desconto', function () {
    $pagamento = Pagamento::factory()->create([
        'contrato_id' => $this->contrato->id,
        'valor' => 1000.00,
        'desconto_comercial' => 0.00,
    ]);

    expect($pagamento->valor_final)->toBe(1000.00);
});

// Listagem inclui desconto

test('lista pagamentos inclui desconto comercial e valor final', function () {
    Pagamento::factory()->create([
        'contrato_id' => $this->contrato->id,
        'valor' => 1000.00,
        'desconto_comercial' => 100.00,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$this->contrato->id}/pagamentos");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'desconto_comercial' => '100.00',
            'valor_final' => 900.00,
        ]);
});
