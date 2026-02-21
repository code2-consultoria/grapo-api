<?php

use App\Models\Contrato;
use App\Models\ContratoAditivo;
use App\Models\ContratoAditivoItem;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(), // Acesso ativo
    ]);
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

// =================================================================
// CRIAÇÃO DE ADITIVOS
// =================================================================

// Cenário: Criar aditivo para contrato ativo
test('cria aditivo para contrato ativo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'prorrogacao',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'descricao' => 'Prorrogação por mais 30 dias',
        'nova_data_termino' => now()->addMonths(2)->format('Y-m-d'),
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.tipo', 'prorrogacao');
    $response->assertJsonPath('data.status', 'rascunho');

    $this->assertDatabaseHas('contrato_aditivos', [
        'contrato_id' => $contrato->id,
        'tipo' => 'prorrogacao',
        'status' => 'rascunho',
    ]);
});

// Cenário: Não cria aditivo para contrato em rascunho
test('nao cria aditivo para contrato em rascunho', function () {
    $contrato = Contrato::factory()->rascunho()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'prorrogacao',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'nova_data_termino' => now()->addMonths(2)->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'contratos ativos'));
});

// Cenário: Não cria aditivo para contrato cancelado
test('nao cria aditivo para contrato cancelado', function () {
    $contrato = Contrato::factory()->cancelado()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'prorrogacao',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'nova_data_termino' => now()->addMonths(2)->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'contratos ativos'));
});

// =================================================================
// ITENS DE ADITIVO
// =================================================================

// Cenário: Adiciona item ao aditivo em rascunho
test('adiciona item ao aditivo em rascunho', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade_alterada' => 5,
            'valor_unitario' => 10.00,
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.quantidade_alterada', 5);

    $this->assertDatabaseHas('contrato_aditivo_itens', [
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_alterada' => 5,
    ]);
});

// Cenário: Não adiciona item ao aditivo ativo
test('nao adiciona item ao aditivo ativo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->ativo()->create([
        'contrato_id' => $contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade_alterada' => 5,
            'valor_unitario' => 10.00,
        ]);

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'não pode ser editado'));
});

// Cenário: Remove item do aditivo em rascunho
test('remove item do aditivo em rascunho', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    $item = ContratoAditivoItem::factory()->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/itens/{$item->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('contrato_aditivo_itens', [
        'id' => $item->id,
    ]);
});

// =================================================================
// LISTAGEM E VISUALIZAÇÃO
// =================================================================

// Cenário: Lista aditivos do contrato
test('lista aditivos do contrato', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    ContratoAditivo::factory()->prorrogacao()->create([
        'contrato_id' => $contrato->id,
    ]);
    ContratoAditivo::factory()->acrescimo()->ativo()->create([
        'contrato_id' => $contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$contrato->id}/aditivos");

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
});

// Cenário: Visualiza aditivo específico
test('visualiza aditivo especifico com itens', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->count(2)->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('data.id', $aditivo->id);
    $response->assertJsonCount(2, 'data.itens');
});

// =================================================================
// ATUALIZAÇÃO DE ADITIVO
// =================================================================

// Cenário: Atualiza aditivo em rascunho
test('atualiza aditivo em rascunho', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $aditivo = ContratoAditivo::factory()->prorrogacao()->create([
        'contrato_id' => $contrato->id,
        'descricao' => 'Descrição original',
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}", [
            'descricao' => 'Nova descrição',
            'nova_data_termino' => now()->addMonths(3)->format('Y-m-d'),
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.descricao', 'Nova descrição');
});

// Cenário: Não atualiza aditivo ativo
test('nao atualiza aditivo ativo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $aditivo = ContratoAditivo::factory()->prorrogacao()->ativo()->create([
        'contrato_id' => $contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}", [
            'descricao' => 'Tentativa de alteração',
        ]);

    $response->assertStatus(422);
});
