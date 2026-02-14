<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

describe('Forgot Password - Solicitacao de Reset', function () {
    test('usuario pode solicitar link de recuperacao de senha', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'usuario@teste.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'email'));

        Notification::assertSentTo($user, ResetPassword::class);
    });

    test('solicitacao com email inexistente retorna sucesso (seguranca)', function () {
        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'naoexiste@teste.com',
        ]);

        // Retorna sucesso para nao revelar se email existe
        $response->assertOk();

        Notification::assertNothingSent();
    });

    test('solicitacao valida campo email', function () {
        $response = $this->postJson('/api/auth/forgot-password', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('solicitacao valida formato do email', function () {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'email-invalido',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });
});

describe('Reset Password - Atualizacao de Senha', function () {
    test('usuario pode resetar senha com token valido', function () {
        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
        ]);

        // Gera token de reset
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'usuario@teste.com',
            'password' => 'NovaSenha@123',
            'password_confirmation' => 'NovaSenha@123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['message']);

        // Verifica que a senha foi atualizada
        $user->refresh();
        expect(Hash::check('NovaSenha@123', $user->password))->toBeTrue();
    });

    test('reset falha com token invalido', function () {
        User::factory()->create([
            'email' => 'usuario@teste.com',
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => 'token-invalido',
            'email' => 'usuario@teste.com',
            'password' => 'NovaSenha@123',
            'password_confirmation' => 'NovaSenha@123',
        ]);

        $response->assertStatus(422);
    });

    test('reset falha com email diferente do token', function () {
        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'outro@teste.com',
            'password' => 'NovaSenha@123',
            'password_confirmation' => 'NovaSenha@123',
        ]);

        $response->assertStatus(422);
    });

    test('reset valida campos obrigatorios', function () {
        $response = $this->postJson('/api/auth/reset-password', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token', 'email', 'password']);
    });

    test('reset valida senha fraca', function () {
        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'usuario@teste.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('reset valida confirmacao de senha', function () {
        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'usuario@teste.com',
            'password' => 'NovaSenha@123',
            'password_confirmation' => 'SenhaDiferente@123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('token e invalidado apos uso', function () {
        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
        ]);

        $token = Password::createToken($user);

        // Primeiro reset - deve funcionar
        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'usuario@teste.com',
            'password' => 'NovaSenha@123',
            'password_confirmation' => 'NovaSenha@123',
        ]);

        $response->assertOk();

        // Segundo reset com mesmo token - deve falhar
        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'usuario@teste.com',
            'password' => 'OutraSenha@123',
            'password_confirmation' => 'OutraSenha@123',
        ]);

        $response->assertStatus(422);
    });
});
