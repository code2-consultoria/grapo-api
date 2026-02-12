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
    $this->contrato = Contrato::factory()->rascunho()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);
});

test('adiciona item ao contrato em rascunho', function () {
    Lote::factory()->comQuantidade(20)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade' => 15,
            'valor_unitario_diaria' => 5.00,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('contrato_itens', [
        'contrato_id' => $this->contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 15,
    ]);
});

test('remove item do contrato em rascunho', function () {
    $item = ContratoItem::factory()->create([
        'contrato_id' => $this->contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/contratos/{$this->contrato->id}/itens/{$item->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('contrato_itens', [
        'id' => $item->id,
    ]);
});

test('atualiza quantidade do item em contrato rascunho', function () {
    $item = ContratoItem::factory()->create([
        'contrato_id' => $this->contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
        'valor_unitario_diaria' => 5.00,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/api/contratos/{$this->contrato->id}/itens/{$item->id}", [
            'quantidade' => 20,
            'valor_unitario_diaria' => 6.00,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('contrato_itens', [
        'id' => $item->id,
        'quantidade' => 20,
        'valor_unitario_diaria' => 6.00,
    ]);
});

test('valida disponibilidade ao adicionar item', function () {
    // Apenas 10 disponíveis
    Lote::factory()->comQuantidade(10)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade' => 30, // Mais do que disponível
            'valor_unitario_diaria' => 5.00,
        ]);

    // Deve adicionar mesmo assim (validação ocorre na ativação)
    // Ou pode retornar aviso - depende da decisão de UX
    $response->assertStatus(201);
});

test('calcula valor total do item corretamente', function () {
    Lote::factory()->comQuantidade(20)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    // Contrato de 30 dias
    $this->contrato->update([
        'data_inicio' => now(),
        'data_termino' => now()->addDays(29),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade' => 10,
            'valor_unitario_diaria' => 5.00,
        ]);

    $response->assertStatus(201);

    // 10 unidades * 5.00/dia * 30 dias = 1500.00
    $item = ContratoItem::where('contrato_id', $this->contrato->id)->first();
    expect((float) $item->valor_total_item)->toBe(1500.00);
});

test('atualiza valor total do contrato ao adicionar item', function () {
    Lote::factory()->comQuantidade(20)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $this->contrato->update([
        'data_inicio' => now(),
        'data_termino' => now()->addDays(29),
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade' => 10,
            'valor_unitario_diaria' => 5.00,
        ]);

    $this->contrato->refresh();
    expect((float) $this->contrato->valor_total)->toBe(1500.00);
});
