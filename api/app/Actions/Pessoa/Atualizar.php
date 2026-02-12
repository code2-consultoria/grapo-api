<?php

namespace App\Actions\Pessoa;

use App\Contracts\Command;
use App\Models\Pessoa;

/**
 * Atualiza uma pessoa existente.
 */
class Atualizar implements Command
{
    public function __construct(
        private Pessoa $pessoa,
        private array $dados
    ) {}

    /**
     * Executa a atualização da pessoa.
     */
    public function handle(): void
    {
        $this->pessoa->fill($this->dados);
        $this->pessoa->save();
    }

    /**
     * Retorna a pessoa atualizada.
     */
    public function getPessoa(): Pessoa
    {
        return $this->pessoa;
    }
}
