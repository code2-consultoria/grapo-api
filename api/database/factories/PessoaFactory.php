<?php

namespace Database\Factories;

use App\Enums\TipoPessoa;
use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pessoa>
 */
class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        return [
            'tipo' => TipoPessoa::Locador,
            'nome' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'telefone' => fake()->phoneNumber(),
            'endereco' => fake()->address(),
            'ativo' => true,
        ];
    }

    /**
     * Pessoa do tipo locador.
     */
    public function locador(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoPessoa::Locador,
            'nome' => fake()->company(),
        ]);
    }

    /**
     * Pessoa do tipo locatário.
     */
    public function locatario(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoPessoa::Locatario,
            'nome' => fake()->name(),
        ]);
    }

    /**
     * Pessoa do tipo responsável financeiro.
     */
    public function responsavelFinanceiro(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoPessoa::ResponsavelFinanceiro,
            'nome' => fake()->name(),
        ]);
    }

    /**
     * Pessoa do tipo responsável administrativo.
     */
    public function responsavelAdministrativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoPessoa::ResponsavelAdministrativo,
            'nome' => fake()->name(),
        ]);
    }

    /**
     * Pessoa inativa.
     */
    public function inativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'ativo' => false,
        ]);
    }
}
