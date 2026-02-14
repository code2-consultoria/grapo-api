<?php

namespace Database\Factories;

use App\Models\Plano;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plano>
 */
class PlanoFactory extends Factory
{
    protected $model = Plano::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->randomElement(['Trimestral', 'Semestral', 'Anual']),
            'duracao_meses' => fake()->randomElement([3, 6, 12]),
            'valor' => fake()->randomElement([75.00, 140.00, 250.00]),
            'ativo' => true,
        ];
    }

    /**
     * Plano com Stripe configurado.
     */
    public function comStripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_product_id' => 'prod_' . fake()->regexify('[A-Za-z0-9]{14}'),
            'stripe_price_id' => 'price_' . fake()->regexify('[A-Za-z0-9]{14}'),
        ]);
    }

    /**
     * Plano trimestral.
     */
    public function trimestral(): static
    {
        return $this->state(fn (array $attributes) => [
            'nome' => 'Trimestral',
            'duracao_meses' => 3,
            'valor' => 75.00,
        ]);
    }

    /**
     * Plano semestral.
     */
    public function semestral(): static
    {
        return $this->state(fn (array $attributes) => [
            'nome' => 'Semestral',
            'duracao_meses' => 6,
            'valor' => 140.00,
        ]);
    }

    /**
     * Plano anual.
     */
    public function anual(): static
    {
        return $this->state(fn (array $attributes) => [
            'nome' => 'Anual',
            'duracao_meses' => 12,
            'valor' => 250.00,
        ]);
    }

    /**
     * Plano inativo.
     */
    public function inativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'ativo' => false,
        ]);
    }
}
