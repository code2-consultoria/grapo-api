<?php

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
});

test('lista tipos de ativos do locador', function () {
    TipoAtivo::factory()->count(3)->create([
        'locador_id' => $this->locador->id,
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/tipos-ativos');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('cria tipo de ativo', function () {
    $response = $this->actingAs($this->user)->postJson('/api/tipos-ativos', [
        'nome' => 'Placa de EVA',
        'descricao' => 'Placa de EVA para proteção de piso',
        'unidade_medida' => 'unidade',
        'valor_mensal_sugerido' => 150.00,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.nome', 'Placa de EVA');
    $response->assertJsonPath('data.valor_mensal_sugerido', '150.00');
    // Accessor: (150 * 1.10) / 30 = 5.50
    $response->assertJsonPath('data.valor_diaria_sugerido', 5.5);

    $this->assertDatabaseHas('tipos_ativos', [
        'locador_id' => $this->locador->id,
        'nome' => 'Placa de EVA',
    ]);
});

test('atualiza tipo de ativo', function () {
    $tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'valor_mensal_sugerido' => 150.00,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/api/tipos-ativos/{$tipoAtivo->id}", [
            'valor_mensal_sugerido' => 225.00,
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.valor_mensal_sugerido', '225.00');
    // Accessor: (225 * 1.10) / 30 = 8.25
    $response->assertJsonPath('data.valor_diaria_sugerido', 8.25);
});

test('exclui tipo de ativo sem lotes', function () {
    $tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/tipos-ativos/{$tipoAtivo->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('tipos_ativos', [
        'id' => $tipoAtivo->id,
    ]);
});

test('não permite nome duplicado no mesmo locador', function () {
    TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Placa de EVA',
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/tipos-ativos', [
        'nome' => 'Placa de EVA',
        'unidade_medida' => 'unidade',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['nome']);
});

test('valida campos obrigatórios', function () {
    $response = $this->actingAs($this->user)->postJson('/api/tipos-ativos', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['nome']);
});

test('retorna quantidade disponível do tipo de ativo', function () {
    $tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
    ]);

    // Cria lotes
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $tipoAtivo->id,
        'quantidade_disponivel' => 10,
    ]);

    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $tipoAtivo->id,
        'quantidade_disponivel' => 5,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/tipos-ativos/{$tipoAtivo->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('data.quantidade_disponivel', 15);
});
