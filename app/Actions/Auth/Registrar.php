<?php

namespace App\Actions\Auth;

use App\Enums\TipoPessoa;
use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;
use Illuminate\Support\Facades\DB;

/**
 * Registra um novo usuario com locador vinculado.
 *
 * Cria:
 * - User (papel: cliente, ativo: true)
 * - Pessoa (tipo: locador)
 * - VinculoTime (liga user ao locador)
 */
class Registrar
{
    public function __construct(
        private string $name,
        private string $email,
        private string $password
    ) {}

    /**
     * Executa o registro.
     *
     * @return array{user: User, locador: Pessoa, token: string}
     */
    public function handle(): array
    {
        return DB::transaction(function () {
            // Cria o usuario
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'papel' => 'cliente',
                'ativo' => true,
            ]);

            // Cria o locador (Pessoa)
            $locador = new Pessoa([
                'tipo' => TipoPessoa::Locador,
                'nome' => $this->name,
                'email' => $this->email,
                'ativo' => true,
            ]);
            $locador->save();

            // Cria o vinculo entre user e locador
            $vinculo = new VinculoTime();
            $vinculo->user()->associate($user);
            $vinculo->locador()->associate($locador);
            $vinculo->save();

            // Gera token de autenticacao
            $token = $user->createToken('auth-token')->plainTextToken;

            return [
                'user' => $user,
                'locador' => $locador,
                'token' => $token,
            ];
        });
    }
}
