<?php

namespace App\Http\Controllers\Auth\Api;

use App\Actions\Auth\Registrar;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Register extends Controller
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Registra um novo usuario via API.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ]);

        $action = new Registrar(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password']
        );

        $result = $action->handle();

        return response()->json([
            'data' => [
                'token' => $result['token'],
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                ],
                'locador' => [
                    'id' => $result['locador']->id,
                    'nome' => $result['locador']->nome,
                    'email' => $result['locador']->email,
                ],
            ],
        ], 201);
    }
}
