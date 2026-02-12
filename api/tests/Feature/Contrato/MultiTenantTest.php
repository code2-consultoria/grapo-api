<?php

use App\Models\Contrato;
use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;

// Cenário: Multi-tenant isolamento
test('usuário visualiza apenas contratos do próprio locador', function () {
    // Locador A com 3 contratos
    $locadorA = Pessoa::factory()->locador()->create();
    $userA = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $userA->id,
        'locador_id' => $locadorA->id,
    ]);
    $locatarioA = Pessoa::factory()->locatario()->create(['locador_id' => $locadorA->id]);

    Contrato::factory()->count(3)->create([
        'locador_id' => $locadorA->id,
        'locatario_id' => $locatarioA->id,
    ]);

    // Locador B com 2 contratos
    $locadorB = Pessoa::factory()->locador()->create();
    $locatarioB = Pessoa::factory()->locatario()->create(['locador_id' => $locadorB->id]);

    Contrato::factory()->count(2)->create([
        'locador_id' => $locadorB->id,
        'locatario_id' => $locatarioB->id,
    ]);

    // Usuário do locador A lista contratos
    $response = $this->actingAs($userA)->getJson('/api/contratos');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);

    // Verifica que todos os contratos são do locador A
    foreach ($response->json('data') as $contrato) {
        expect($contrato['locador_id'])->toBe($locadorA->id);
    }
});

test('usuário não pode acessar contrato de outro locador', function () {
    $locadorA = Pessoa::factory()->locador()->create();
    $userA = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $userA->id,
        'locador_id' => $locadorA->id,
    ]);

    $locadorB = Pessoa::factory()->locador()->create();
    $locatarioB = Pessoa::factory()->locatario()->create(['locador_id' => $locadorB->id]);
    $contratoB = Contrato::factory()->create([
        'locador_id' => $locadorB->id,
        'locatario_id' => $locatarioB->id,
    ]);

    // Usuário do locador A tenta acessar contrato do locador B
    $response = $this->actingAs($userA)->getJson("/api/contratos/{$contratoB->id}");

    $response->assertStatus(404);
});

test('usuário não pode criar contrato com locatário de outro locador', function () {
    $locadorA = Pessoa::factory()->locador()->create();
    $userA = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $userA->id,
        'locador_id' => $locadorA->id,
    ]);

    $locadorB = Pessoa::factory()->locador()->create();
    $locatarioB = Pessoa::factory()->locatario()->create(['locador_id' => $locadorB->id]);

    $response = $this->actingAs($userA)->postJson('/api/contratos', [
        'locatario_id' => $locatarioB->id,
        'data_inicio' => now()->addDay()->format('Y-m-d'),
        'data_termino' => now()->addMonth()->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
});

test('admin pode visualizar contratos de qualquer locador', function () {
    $admin = User::factory()->create([
        'papel' => 'admin',
    ]);

    $locadorA = Pessoa::factory()->locador()->create();
    $locatarioA = Pessoa::factory()->locatario()->create(['locador_id' => $locadorA->id]);
    Contrato::factory()->count(3)->create([
        'locador_id' => $locadorA->id,
        'locatario_id' => $locatarioA->id,
    ]);

    $locadorB = Pessoa::factory()->locador()->create();
    $locatarioB = Pessoa::factory()->locatario()->create(['locador_id' => $locadorB->id]);
    Contrato::factory()->count(2)->create([
        'locador_id' => $locadorB->id,
        'locatario_id' => $locatarioB->id,
    ]);

    // Admin vê todos os contratos
    $response = $this->actingAs($admin)->getJson('/api/contratos');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(5);
});
