<?php

use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->admin = User::factory()->create(['papel' => 'admin']);
});

test('cria locador com documento CPF', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/pessoas', [
        'tipo' => 'locador',
        'nome' => 'Locador Pessoa Física',
        'documento' => '52998224725',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.tipo', 'locador');

    $this->assertDatabaseHas('documentos', [
        'tipo' => 'cpf',
        'numero' => '52998224725',
    ]);
});

test('cria locador com documento CNPJ', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/pessoas', [
        'tipo' => 'locador',
        'nome' => 'Locador Empresa',
        'documento' => '11222333000181',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.tipo', 'locador');

    $this->assertDatabaseHas('documentos', [
        'tipo' => 'cnpj',
        'numero' => '11222333000181',
    ]);
});

test('rejeita locador com documento duplicado', function () {
    // Cria primeiro locador
    $this->actingAs($this->admin)->postJson('/api/pessoas', [
        'tipo' => 'locador',
        'nome' => 'Primeiro Locador',
        'documento' => '52998224725',
    ]);

    // Tenta criar segundo locador com mesmo documento
    $response = $this->actingAs($this->admin)->postJson('/api/pessoas', [
        'tipo' => 'locador',
        'nome' => 'Segundo Locador',
        'documento' => '52998224725',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});

test('campo documento é obrigatório para locador', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/pessoas', [
        'tipo' => 'locador',
        'nome' => 'Locador Sem Documento',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});
