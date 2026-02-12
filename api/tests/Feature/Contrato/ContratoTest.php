<?php

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
    $this->locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);
    $this->tipoAtivo = TipoAtivo::factory()->placaEva()->create([
        'locador_id' => $this->locador->id,
    ]);
});

// Cenário: Criar contrato em rascunho
test('cria contrato em rascunho', function () {
    $response = $this->actingAs($this->user)->postJson('/api/contratos', [
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now()->addDay()->format('Y-m-d'),
        'data_termino' => now()->addMonth()->format('Y-m-d'),
        'observacoes' => 'Contrato de teste',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.status', 'rascunho');

    $this->assertDatabaseHas('contratos', [
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => 'rascunho',
    ]);
});

// Cenário: Contrato criado não aloca lotes
test('contrato em rascunho não aloca lotes', function () {
    $lote = Lote::factory()->comQuantidade(20)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $contrato = Contrato::factory()->rascunho()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    // Adiciona item ao contrato
    $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/itens", [
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
        'valor_unitario_diaria' => 5.00,
    ]);

    // Verifica que lote não foi alterado
    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(20);
});

// Cenário: Ativar contrato com alocação FIFO
test('ativa contrato e aloca lotes usando FIFO', function () {
    // Cria lotes
    $loteAntigo = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-ANTIGO',
        'quantidade_total' => 12,
        'quantidade_disponivel' => 12,
        'data_aquisicao' => now()->subYear(),
        'created_at' => now()->subYear(),
    ]);

    $loteRecente = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-RECENTE',
        'quantidade_total' => 10,
        'quantidade_disponivel' => 10,
        'data_aquisicao' => now()->subMonth(),
        'created_at' => now()->subMonth(),
    ]);

    // Cria contrato com item
    $contrato = Contrato::factory()->rascunho()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 15,
        'valor_unitario_diaria' => 5.00,
        'valor_total_item' => 15 * 5.00 * 30,
    ]);

    // Ativa o contrato
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/ativar");

    $response->assertStatus(200);
    $response->assertJsonPath('data.status', 'ativo');

    // Verifica alocação FIFO
    $loteAntigo->refresh();
    $loteRecente->refresh();
    expect($loteAntigo->quantidade_disponivel)->toBe(0);
    expect($loteRecente->quantidade_disponivel)->toBe(7);
});

// Cenário: Erro ao ativar sem disponibilidade
test('retorna erro ao ativar contrato sem disponibilidade suficiente', function () {
    $lote = Lote::factory()->comQuantidade(10)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $contrato = Contrato::factory()->rascunho()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 30, // Mais do que disponível
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/ativar");

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'Quantidade indisponível'));

    // Contrato permanece em rascunho
    $contrato->refresh();
    expect($contrato->status)->toBe('rascunho');

    // Lote não foi alterado
    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(10);
});

// Cenário: Cancelar contrato libera itens
test('cancela contrato ativo e libera itens alocados', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 5, // 15 já alocados
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 15,
    ]);

    // Cria alocação
    \App\Models\AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $lote->id,
        'quantidade_alocada' => 15,
    ]);

    // Cancela o contrato
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/cancelar");

    $response->assertStatus(200);
    $response->assertJsonPath('data.status', 'cancelado');

    // Verifica que itens foram liberados
    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(20);
});

// Cenário: Erro ao editar contrato ativo
test('retorna erro ao tentar editar contrato ativo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    // Tenta adicionar item
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade' => 5,
            'valor_unitario_diaria' => 5.00,
        ]);

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'não pode ser alterado'));
});

// Cenário: Finalizar contrato
test('finaliza contrato ativo e libera itens', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 10,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    \App\Models\AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $lote->id,
        'quantidade_alocada' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/finalizar");

    $response->assertStatus(200);
    $response->assertJsonPath('data.status', 'finalizado');

    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(20);
});
