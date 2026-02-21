<?php

use App\Models\Pessoa;
use App\Models\Plano;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->plano = Plano::factory()->create([
        'nome' => 'Trimestral',
        'duracao_meses' => 3,
        'valor' => 75.00,
        'ativo' => true,
    ]);
});

// Testes de Trial

test('locador criado tem data_limite_acesso null por padrao', function () {
    $locador = Pessoa::factory()->locador()->create();

    expect($locador->data_limite_acesso)->toBeNull();
    expect($locador->hasAcessoAtivo())->toBeFalse();
});

test('definir trial define data_limite_acesso para 7 dias', function () {
    $locador = Pessoa::factory()->locador()->create();

    $locador->definirTrial();

    expect($locador->data_limite_acesso->format('Y-m-d'))
        ->toBe(now()->addDays(7)->format('Y-m-d'));
    expect($locador->hasAcessoAtivo())->toBeTrue();
});

test('locador com trial ativo tem acesso', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addDays(5),
    ]);

    expect($locador->hasAcessoAtivo())->toBeTrue();
});

test('locador com trial expirado nao tem acesso', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->subDay(),
    ]);

    expect($locador->hasAcessoAtivo())->toBeFalse();
});

// Testes de Pagamento

test('pagamento atualiza data_limite_acesso para 60 dias', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addDays(5),
    ]);

    $locador->atualizarAcessoPorPagamento();

    expect($locador->data_limite_acesso->format('Y-m-d'))
        ->toBe(now()->addDays(60)->format('Y-m-d'));
    expect($locador->hasAcessoAtivo())->toBeTrue();
});

// Testes de Cancelamento

test('cancelamento reduz data_limite_acesso para 30 dias', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addDays(60),
    ]);

    $locador->definirAcessoCancelamento();

    expect($locador->data_limite_acesso->format('Y-m-d'))
        ->toBe(now()->addDays(30)->format('Y-m-d'));
});

test('cancelamento nao aumenta data_limite_acesso se ja menor que 30 dias', function () {
    $dataOriginal = now()->addDays(10);
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => $dataOriginal,
    ]);

    $locador->definirAcessoCancelamento();

    expect($locador->data_limite_acesso->format('Y-m-d'))
        ->toBe($dataOriginal->format('Y-m-d'));
});

// Testes de Registro com Trial

test('registro cria locador com trial de 7 dias', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Novo Locador',
        'email' => 'novo@locador.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'accepted_terms' => true,
    ]);

    $response->assertStatus(201);

    $user = User::where('email', 'novo@locador.com')->first();
    $locador = $user->locador();

    expect($locador)->not->toBeNull();
    expect($locador->data_limite_acesso->format('Y-m-d'))
        ->toBe(now()->addDays(7)->format('Y-m-d'));
});

// Testes do Middleware

test('locador com acesso ativo pode acessar contratos', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addDays(30),
    ]);
    $user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $user->id,
        'locador_id' => $locador->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/contratos');

    $response->assertStatus(200);
});

test('locador com acesso expirado nao pode acessar contratos', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->subDay(),
    ]);
    $user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $user->id,
        'locador_id' => $locador->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/contratos');

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Assinatura expirada. Renove para continuar acessando.',
        ]);
});

test('locador sem data_limite_acesso nao pode acessar contratos', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => null,
    ]);
    $user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $user->id,
        'locador_id' => $locador->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/contratos');

    $response->assertStatus(403);
});

test('locador com acesso expirado pode acessar dashboard', function () {
    $locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->subDay(),
    ]);
    $user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $user->id,
        'locador_id' => $locador->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/dashboard');

    $response->assertStatus(200);
});

test('admin nao e afetado pelo middleware de assinatura', function () {
    $admin = User::factory()->create(['papel' => 'admin']);

    $response = $this->actingAs($admin)->getJson('/api/contratos');

    // Admin pode acessar mesmo sem locador vinculado
    $response->assertStatus(200);
});
