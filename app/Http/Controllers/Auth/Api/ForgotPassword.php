<?php

namespace App\Http\Controllers\Auth\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPassword extends Controller
{
    /**
     * Envia email com link de recuperacao de senha.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Envia o link de reset (se o email existir)
        Password::sendResetLink(
            $request->only('email')
        );

        // Sempre retorna sucesso para nao revelar se o email existe
        return response()->json([
            'message' => 'Se o email estiver cadastrado, você receberá um link de recuperação.',
        ]);
    }
}
