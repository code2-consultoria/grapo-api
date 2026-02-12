<?php

namespace App\Http\Controllers\Auth\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Login extends Controller
{
    /**
     * Autentica o usuário e retorna um token de acesso.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        if (! $user->ativo) {
            return response()->json([
                'message' => 'Usuário inativo. Entre em contato com o administrador.',
            ], 403);
        }

        $deviceName = $validated['device_name'] ?? ($request->userAgent() ?? 'unknown');
        $token = $user->createToken($deviceName);

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'papel' => $user->papel,
                ],
            ],
            'message' => 'Login realizado com sucesso.',
        ]);
    }
}
