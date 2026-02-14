<?php

use App\Models\AlocacaoLote;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create();
    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);
    $this->tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
    ]);
});

test('lista lotes do locador', function () {
    Lote::factory()->count(3)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/lotes');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('cria lote', function () {
    $response = $this->actingAs($this->user)->postJson('/api/lotes', [
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOT-001',
        'quantidade_total' => 20,
        'fornecedor' => 'Fornecedor Teste',
        'valor_total' => 1000.00,
        'valor_frete' => 100.00,
        'forma_pagamento' => 'pix',
        'nf' => 'NF-123456',
        'data_aquisicao' => now()->format('Y-m-d'),
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.codigo', 'LOT-001');
    $response->assertJsonPath('data.quantidade_total', 20);
    $response->assertJsonPath('data.quantidade_disponivel', 20);
    $response->assertJsonPath('data.fornecedor', 'Fornecedor Teste');
    $response->assertJsonPath('data.custo_aquisicao', '1100.00'); // valor_total + valor_frete

    $this->assertDatabaseHas('lotes', [
        'locador_id' => $this->locador->id,
        'codigo' => 'LOT-001',
        'quantidade_total' => 20,
        'quantidade_disponivel' => 20,
        'fornecedor' => 'Fornecedor Teste',
    ]);
});

test('atualiza lote', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'valor_total' => 500.00,
        'valor_frete' => 50.00,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/api/lotes/{$lote->id}", [
            'fornecedor' => 'Novo Fornecedor',
            'valor_total' => 800.00,
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.fornecedor', 'Novo Fornecedor');
    $response->assertJsonPath('data.valor_total', '800.00');
    // custo_aquisicao recalculado: 800 + 50 = 850
    $response->assertJsonPath('data.custo_aquisicao', '850.00');
});

test('exclui lote sem alocações', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/lotes/{$lote->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('lotes', [
        'id' => $lote->id,
    ]);
});

test('não permite excluir lote com alocações ativas', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_disponivel' => 5,
    ]);

    $locatario = Pessoa::factory()->locatario()->create(['locador_id' => $this->locador->id]);
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
    ]);

    $item = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $lote->id,
        'quantidade_alocada' => 15,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/lotes/{$lote->id}");

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'alocações ativas'));
});

test('não permite código duplicado no mesmo locador', function () {
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOT-001',
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/lotes', [
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOT-001',
        'quantidade_total' => 10,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['codigo']);
});

test('valida campos obrigatórios', function () {
    $response = $this->actingAs($this->user)->postJson('/api/lotes', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'tipo_ativo_id',
        'codigo',
        'quantidade_total',
    ]);
});

test('filtra lotes por tipo de ativo', function () {
    $tipoAtivo2 = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
    ]);

    Lote::factory()->count(3)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    Lote::factory()->count(2)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $tipoAtivo2->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes?tipo_ativo_id={$this->tipoAtivo->id}");

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('lista apenas lotes disponíveis', function () {
    Lote::factory()->count(3)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'status' => 'disponivel',
    ]);

    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'status' => 'indisponivel',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/lotes?status=disponivel');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('calcula custo unitario corretamente', function () {
    $response = $this->actingAs($this->user)->postJson('/api/lotes', [
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOT-CUSTO',
        'quantidade_total' => 10,
        'valor_total' => 900.00,
        'valor_frete' => 100.00,
    ]);

    $response->assertStatus(201);
    // custo_unitario = (900 + 100) / 10 = 100
    expect((float) $response->json('data.custo_unitario'))->toBe(100.0);
});
