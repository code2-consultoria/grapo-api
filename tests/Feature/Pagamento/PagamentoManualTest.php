<?php

use App\Enums\OrigemPagamento;
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

// Registrar pagamento manual

test('registra pagamento manual recebido', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'data_vencimento' => '2026-02-15',
            'data_pagamento' => '2026-02-14',
            'origem' => 'manual',
            'observacoes' => 'Pagamento em dinheiro',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'valor' => '1500.00',
                'status' => 'pago',
                'origem' => 'manual',
            ],
        ]);

    expect($this->contrato->pagamentos()->count())->toBe(1);
});

test('registra pagamento pendente', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'data_vencimento' => '2026-03-15',
            'origem' => 'manual',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'status' => 'pendente',
            ],
        ]);
});

test('valida valor obrigatorio', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'data_vencimento' => '2026-02-15',
            'origem' => 'manual',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['valor']);
});

test('valida data vencimento obrigatoria', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'origem' => 'manual',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data_vencimento']);
});

test('valida origem obrigatoria', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'data_vencimento' => '2026-02-15',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['origem']);
});

test('valida origem invalida', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos", [
            'valor' => 1500.00,
            'data_vencimento' => '2026-02-15',
            'origem' => 'invalida',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['origem']);
});

// Listar pagamentos

test('lista pagamentos do contrato', function () {
    Pagamento::factory()->count(3)->create([
        'contrato_id' => $this->contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$this->contrato->id}/pagamentos");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('lista pagamentos ordenados por data de vencimento', function () {
    Pagamento::factory()->create([
        'contrato_id' => $this->contrato->id,
        'data_vencimento' => '2026-03-15',
    ]);
    Pagamento::factory()->create([
        'contrato_id' => $this->contrato->id,
        'data_vencimento' => '2026-02-15',
    ]);
    Pagamento::factory()->create([
        'contrato_id' => $this->contrato->id,
        'data_vencimento' => '2026-04-15',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$this->contrato->id}/pagamentos");

    $response->assertStatus(200);

    $datas = collect($response->json('data'))->pluck('data_vencimento')->toArray();
    expect($datas)->toBe(['2026-02-15', '2026-03-15', '2026-04-15']);
});

// Marcar como pago

test('marca pagamento pendente como pago', function () {
    $pagamento = Pagamento::factory()->pendente()->create([
        'contrato_id' => $this->contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos/{$pagamento->id}/pagar", [
            'data_pagamento' => '2026-02-14',
            'observacoes' => 'Pago via PIX',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'status' => 'pago',
            ],
        ]);

    $pagamento->refresh();
    expect($pagamento->status)->toBe(StatusPagamento::Pago);
    expect($pagamento->data_pagamento->format('Y-m-d'))->toBe('2026-02-14');
});

test('nao permite marcar pagamento ja pago', function () {
    $pagamento = Pagamento::factory()->pago()->create([
        'contrato_id' => $this->contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/pagamentos/{$pagamento->id}/pagar");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Pagamento ja foi realizado.',
        ]);
});

// Cancelar pagamento

test('cancela pagamento pendente', function () {
    $pagamento = Pagamento::factory()->pendente()->create([
        'contrato_id' => $this->contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/contratos/{$this->contrato->id}/pagamentos/{$pagamento->id}");

    $response->assertStatus(200);

    $pagamento->refresh();
    expect($pagamento->status)->toBe(StatusPagamento::Cancelado);
});

test('nao permite cancelar pagamento ja pago', function () {
    $pagamento = Pagamento::factory()->pago()->create([
        'contrato_id' => $this->contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/contratos/{$this->contrato->id}/pagamentos/{$pagamento->id}");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Pagamento ja realizado nao pode ser cancelado.',
        ]);
});

// Resumo de pagamentos

test('retorna resumo de pagamentos do contrato', function () {
    Pagamento::factory()->pago()->create([
        'contrato_id' => $this->contrato->id,
        'valor' => 500.00,
    ]);
    Pagamento::factory()->pago()->create([
        'contrato_id' => $this->contrato->id,
        'valor' => 500.00,
    ]);
    Pagamento::factory()->pendente()->create([
        'contrato_id' => $this->contrato->id,
        'valor' => 500.00,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$this->contrato->id}/pagamentos/resumo");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'total_contrato' => '1500.00',
                'total_pago' => '1000.00',
                'total_pendente' => '500.00',
                'qtd_pagamentos' => 3,
                'qtd_pagos' => 2,
                'qtd_pendentes' => 1,
            ],
        ]);
});
