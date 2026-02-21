<?php

use App\Models\Plano;

test('lista planos ativos', function () {
    Plano::factory()->count(3)->create(['ativo' => true]);
    Plano::factory()->count(2)->create(['ativo' => false]);

    $response = $this->getJson('/api/planos');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('lista planos ordenados por duracao', function () {
    Plano::factory()->create(['nome' => 'Anual', 'duracao_meses' => 12, 'ativo' => true]);
    Plano::factory()->create(['nome' => 'Trimestral', 'duracao_meses' => 3, 'ativo' => true]);
    Plano::factory()->create(['nome' => 'Semestral', 'duracao_meses' => 6, 'ativo' => true]);

    $response = $this->getJson('/api/planos');

    $response->assertStatus(200);
    $planos = $response->json('data');
    expect($planos[0]['duracao_meses'])->toBe(3);
    expect($planos[1]['duracao_meses'])->toBe(6);
    expect($planos[2]['duracao_meses'])->toBe(12);
});

test('exibe plano específico', function () {
    $plano = Plano::factory()->create([
        'nome' => 'Anual',
        'duracao_meses' => 12,
        'valor' => 250.00,
        'ativo' => true,
    ]);

    $response = $this->getJson("/api/planos/{$plano->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('data.nome', 'Anual');
    $response->assertJsonPath('data.duracao_meses', 12);
    $response->assertJsonPath('data.valor', '250.00');
});

test('retorna 404 para plano inexistente', function () {
    $response = $this->getJson('/api/planos/'.\Illuminate\Support\Str::uuid());

    $response->assertStatus(404);
});

test('não exibe plano inativo', function () {
    $plano = Plano::factory()->create(['ativo' => false]);

    $response = $this->getJson("/api/planos/{$plano->id}");

    $response->assertStatus(404);
});

test('retorna campos necessários do plano', function () {
    $plano = Plano::factory()->create([
        'nome' => 'Semestral',
        'duracao_meses' => 6,
        'valor' => 140.00,
        'stripe_product_id' => 'prod_test123',
        'stripe_price_id' => 'price_test123',
        'ativo' => true,
    ]);

    $response = $this->getJson("/api/planos/{$plano->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'nome',
            'duracao_meses',
            'valor',
            'stripe_price_id',
        ],
    ]);
});
