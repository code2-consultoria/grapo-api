<?php

use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addDays(30),
    ]);
    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);
});

// Testes do Model

test('locador tem majoracao_diaria padrao de 10%', function () {
    expect((float) $this->locador->majoracao_diaria)->toBe(10.00);
});

test('valor_diaria_sugerido usa majoracao do locador', function () {
    // Majoracao padrao de 10%
    $tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'valor_mensal_sugerido' => 300.00,
    ]);

    // (300 * 1.10) / 30 = 11.00
    expect($tipoAtivo->valor_diaria_sugerido)->toBe(11.00);
});

test('valor_diaria_sugerido usa majoracao personalizada do locador', function () {
    $this->locador->update(['majoracao_diaria' => 20.00]);

    $tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'valor_mensal_sugerido' => 300.00,
    ]);

    // (300 * 1.20) / 30 = 12.00
    expect($tipoAtivo->valor_diaria_sugerido)->toBe(12.00);
});

test('valor_diaria_sugerido com majoracao zero', function () {
    $this->locador->update(['majoracao_diaria' => 0]);

    $tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'valor_mensal_sugerido' => 300.00,
    ]);

    // (300 * 1.00) / 30 = 10.00
    expect($tipoAtivo->valor_diaria_sugerido)->toBe(10.00);
});

// Testes do Endpoint

test('atualiza majoracao_diaria do locador', function () {
    $response = $this->actingAs($this->user)
        ->putJson('/api/perfil/majoracao', [
            'majoracao_diaria' => 15.00,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'majoracao_diaria' => '15.00',
            ],
        ]);

    $this->locador->refresh();
    expect((float) $this->locador->majoracao_diaria)->toBe(15.00);
});

test('nao permite majoracao negativa', function () {
    $response = $this->actingAs($this->user)
        ->putJson('/api/perfil/majoracao', [
            'majoracao_diaria' => -5.00,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['majoracao_diaria']);
});

test('permite majoracao zero', function () {
    $response = $this->actingAs($this->user)
        ->putJson('/api/perfil/majoracao', [
            'majoracao_diaria' => 0,
        ]);

    $response->assertStatus(200);

    $this->locador->refresh();
    expect((float) $this->locador->majoracao_diaria)->toBe(0.00);
});

test('retorna majoracao atual no endpoint de status', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/perfil/majoracao');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'majoracao_diaria' => '10.00',
            ],
        ]);
});
