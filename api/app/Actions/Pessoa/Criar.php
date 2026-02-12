<?php

namespace App\Actions\Pessoa;

use App\Contracts\Command;
use App\Enums\TipoPessoa;
use App\Models\Pessoa;

/**
 * Cria uma nova pessoa (locador ou locatário).
 */
class Criar implements Command
{
    private Pessoa $pessoa;

    public function __construct(
        private TipoPessoa $tipo,
        private string $nome,
        private ?string $email = null,
        private ?string $telefone = null,
        private ?string $endereco = null,
        private ?Pessoa $locador = null
    ) {}

    /**
     * Executa a criação da pessoa.
     */
    public function handle(): void
    {
        $this->pessoa = new Pessoa([
            'tipo' => $this->tipo,
            'nome' => $this->nome,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'endereco' => $this->endereco,
            'ativo' => true,
        ]);

        // Associa o locador para locatários, responsáveis financeiros e administrativos
        if ($this->locador && $this->tipo !== TipoPessoa::Locador) {
            $this->pessoa->locador()->associate($this->locador);
        }

        $this->pessoa->save();
    }

    /**
     * Retorna a pessoa criada.
     */
    public function getPessoa(): Pessoa
    {
        return $this->pessoa;
    }
}
