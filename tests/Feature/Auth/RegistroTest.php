<?php

use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;

describe('Registro de Usuario via API', function () {
    test('usuario pode se registrar com dados validos', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'João Silva',
            'email' => 'joao@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
            'accepted_terms' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'name', 'email'],
                'locador' => ['id', 'nome', 'email'],
            ],
        ]);

        // Verifica que o usuario foi criado
        $user = User::where('email', 'joao@teste.com')->first();
        expect($user)->not->toBeNull();
        expect($user->name)->toBe('João Silva');
        expect($user->papel)->toBe('cliente');
        expect($user->ativo)->toBeTrue();
        expect($user->accepted_terms_at)->not->toBeNull();
    });

    test('registro cria locador automaticamente', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Maria Santos',
            'email' => 'maria@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
            'accepted_terms' => true,
        ]);

        $response->assertStatus(201);

        // Verifica que o locador foi criado
        $locador = Pessoa::where('email', 'maria@teste.com')->first();
        expect($locador)->not->toBeNull();
        expect($locador->nome)->toBe('Maria Santos');
        expect($locador->tipo->value)->toBe('locador');
        expect($locador->ativo)->toBeTrue();
    });

    test('registro vincula usuario ao locador', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Pedro Costa',
            'email' => 'pedro@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
            'accepted_terms' => true,
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'pedro@teste.com')->first();
        $locador = Pessoa::where('email', 'pedro@teste.com')->first();

        // Verifica vinculo
        $vinculo = VinculoTime::where('user_id', $user->id)->first();
        expect($vinculo)->not->toBeNull();
        expect($vinculo->locador_id)->toBe($locador->id);

        // Verifica metodo locador() do user
        expect($user->locador())->not->toBeNull();
        expect($user->locador()->id)->toBe($locador->id);
    });

    test('registro retorna token de autenticacao', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ana Lima',
            'email' => 'ana@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
            'accepted_terms' => true,
        ]);

        $response->assertStatus(201);

        $token = $response->json('data.token');
        expect($token)->not->toBeNull();

        // Verifica que o token funciona
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $meResponse->assertOk();
        $meResponse->assertJsonPath('data.user.email', 'ana@teste.com');
    });
});

describe('Validacoes de Registro', function () {
    test('registro falha sem aceitar termos de uso', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'teste@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['accepted_terms']);
    });

    test('registro falha com termos recusados', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'teste@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
            'accepted_terms' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['accepted_terms']);
    });

    test('registro falha sem nome', function () {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'teste@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('registro falha sem email', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('registro falha com email invalido', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'email-invalido',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('registro falha com email duplicado', function () {
        User::factory()->create(['email' => 'existente@teste.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'existente@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('registro falha sem senha', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'teste@teste.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('registro falha com senha fraca', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'teste@teste.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('registro falha com senha sem confirmacao', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'teste@teste.com',
            'password' => 'Senha@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('registro falha com confirmacao diferente', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'teste@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'OutraSenha@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });
});
