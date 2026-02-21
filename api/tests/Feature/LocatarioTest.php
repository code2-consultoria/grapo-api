<?php

use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(), // Assinatura ativa
    ]);
    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);
});

test('lista locatários', function () {
    // Cria locatários do locador
    Pessoa::factory()->locatario()->count(3)->create([
        'locador_id' => $this->locador->id,
    ]);

    // Locatário de outro locador (não deve aparecer)
    $outroLocador = Pessoa::factory()->locador()->create();
    Pessoa::factory()->locatario()->create([
        'locador_id' => $outroLocador->id,
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/locatarios');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('cria locatário pessoa física', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'João da Silva',
        'email' => 'joao@email.com',
        'telefone' => '(11) 99999-9999',
        'endereco' => 'Rua das Flores, 123',
        'documentos' => [
            ['tipo' => 'cpf', 'numero' => '52998224725'],
        ],
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.nome', 'João da Silva');
    $response->assertJsonPath('data.tipo', 'locatario');

    $this->assertDatabaseHas('pessoas', [
        'tipo' => 'locatario',
        'nome' => 'João da Silva',
    ]);

    $this->assertDatabaseHas('documentos', [
        'tipo' => 'cpf',
        'numero' => '52998224725',
    ]);
});

test('cria locatário pessoa jurídica', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Empresa XYZ LTDA',
        'email' => 'contato@xyz.com',
        'telefone' => '(11) 3333-3333',
        'endereco' => 'Av. Principal, 456',
        'documentos' => [
            ['tipo' => 'cnpj', 'numero' => '11222333000181'],
        ],
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.tipo', 'locatario');
});

test('atualiza locatário', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Nome Antigo',
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/api/locatarios/{$locatario->id}", [
            'nome' => 'Nome Atualizado',
            'email' => 'novo@email.com',
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.nome', 'Nome Atualizado');
});

test('desativa locatário', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/locatarios/{$locatario->id}");

    $response->assertStatus(200);

    $this->assertDatabaseHas('pessoas', [
        'id' => $locatario->id,
        'ativo' => false,
    ]);
});

test('valida campos obrigatórios', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['nome']);
});

test('visualiza detalhes do locatário', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/locatarios/{$locatario->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('data.id', $locatario->id);
});

test('retorna tipo_pessoa PF para locatário com CPF', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);
    \App\Models\Documento::factory()->cpf()->create([
        'pessoa_id' => $locatario->id,
    ]);

    $locatario->refresh();
    expect($locatario->tipo_pessoa)->toBe('PF');
});

test('retorna tipo_pessoa PJ para locatário com CNPJ', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);
    \App\Models\Documento::factory()->cnpj()->create([
        'pessoa_id' => $locatario->id,
    ]);

    $locatario->refresh();
    expect($locatario->tipo_pessoa)->toBe('PJ');
});

// Testes para campo documento (string única com detecção automática)

test('cria locatário com campo documento detectando CPF automaticamente', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Maria Santos',
        'documento' => '52998224725', // CPF válido - 11 dígitos
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.nome', 'Maria Santos');

    $this->assertDatabaseHas('documentos', [
        'tipo' => 'cpf',
        'numero' => '52998224725',
    ]);
});

test('cria locatário com campo documento detectando CNPJ automaticamente', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Empresa ABC',
        'documento' => '11222333000181', // CNPJ válido - 14 dígitos
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.nome', 'Empresa ABC');

    $this->assertDatabaseHas('documentos', [
        'tipo' => 'cnpj',
        'numero' => '11222333000181',
    ]);
});

test('cria locatário com campo documento formatado (com pontuação)', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Pedro Oliveira',
        'documento' => '529.982.247-25', // CPF com formatação
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('documentos', [
        'tipo' => 'cpf',
        'numero' => '52998224725', // Deve ser salvo sem formatação
    ]);
});

test('rejeita documento com tamanho inválido', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Teste Invalido',
        'documento' => '123456789', // Nem CPF (11) nem CNPJ (14)
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});

test('rejeita CPF inválido (dígito verificador errado)', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'CPF Invalido',
        'documento' => '12345678901', // CPF com dígitos verificadores errados
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});

test('rejeita CNPJ inválido (dígito verificador errado)', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'CNPJ Invalido',
        'documento' => '12345678000199', // CNPJ com dígitos verificadores errados
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});

// Testes de unicidade de documento

test('rejeita locatário com documento duplicado no mesmo locador', function () {
    // Cria primeiro locatário com CPF
    $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Primeiro Locatário',
        'documento' => '52998224725',
    ]);

    // Tenta criar segundo locatário com mesmo CPF
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Segundo Locatário',
        'documento' => '52998224725',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});

test('permite mesmo documento para locatários de locadores diferentes', function () {
    // Cria locatário do primeiro locador
    $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Locatário do Locador 1',
        'documento' => '52998224725',
    ]);

    // Cria outro locador e usuário
    $outroLocador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(), // Assinatura ativa
    ]);
    $outroUser = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $outroUser->id,
        'locador_id' => $outroLocador->id,
    ]);

    // Cria locatário do segundo locador com mesmo documento
    $response = $this->actingAs($outroUser)->postJson('/api/locatarios', [
        'nome' => 'Locatário do Locador 2',
        'documento' => '52998224725',
    ]);

    $response->assertStatus(201);
});

test('campo documento é obrigatório para locatário', function () {
    $response = $this->actingAs($this->user)->postJson('/api/locatarios', [
        'nome' => 'Sem Documento',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});
