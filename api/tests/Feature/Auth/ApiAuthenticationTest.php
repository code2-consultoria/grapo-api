<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('usuário pode fazer login via API e recebe token', function () {
    $user = User::factory()->create([
        'email' => 'usuario@teste.com',
        'password' => bcrypt('senha123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'usuario@teste.com',
        'password' => 'senha123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'token',
            'user' => ['id', 'name', 'email'],
        ],
    ]);

    // Verifica que o token foi criado
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->exists())->toBeTrue();
});

test('login falha com credenciais inválidas', function () {
    User::factory()->create([
        'email' => 'usuario@teste.com',
        'password' => bcrypt('senha123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'usuario@teste.com',
        'password' => 'senha_errada',
    ]);

    $response->assertStatus(401);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'Credenciais inválidas'));
});

test('login falha com email inexistente', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'naoexiste@teste.com',
        'password' => 'senha123',
    ]);

    $response->assertStatus(401);
});

test('login valida campos obrigatórios', function () {
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email', 'password']);
});

test('login falha para usuário inativo', function () {
    User::factory()->inativo()->create([
        'email' => 'inativo@teste.com',
        'password' => bcrypt('senha123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'inativo@teste.com',
        'password' => 'senha123',
    ]);

    $response->assertStatus(403);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'inativo'));
});

test('usuário autenticado pode fazer logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
        ->postJson('/api/auth/logout');

    $response->assertStatus(200);

    // Verifica que o token foi revogado
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->exists())->toBeFalse();
});

test('logout sem autenticação retorna 401', function () {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401);
});

test('usuário autenticado pode obter seus dados', function () {
    $user = User::factory()->create([
        'name' => 'João Silva',
        'email' => 'joao@teste.com',
    ]);
    $token = $user->createToken('test-token');

    $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
        ->getJson('/api/auth/me');

    $response->assertStatus(200);
    $response->assertJsonPath('data.name', 'João Silva');
    $response->assertJsonPath('data.email', 'joao@teste.com');
});

test('me sem autenticação retorna 401', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);
});

test('token expirado não permite acesso', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    // Expira o token manualmente
    PersonalAccessToken::where('tokenable_id', $user->id)->delete();

    $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
        ->getJson('/api/auth/me');

    $response->assertStatus(401);
});
