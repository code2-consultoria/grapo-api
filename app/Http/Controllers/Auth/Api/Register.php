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
            'accepted_terms' => ['required', 'accepted'],
        ], [
            'accepted_terms.required' => 'Voce deve aceitar os termos de uso.',
            'accepted_terms.accepted' => 'Voce deve aceitar os termos de uso.',
        ]);

        $action = new Registrar(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            acceptedTerms: true
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
