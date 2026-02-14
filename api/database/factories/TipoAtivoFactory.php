<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\TipoAtivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoAtivo>
 */
class TipoAtivoFactory extends Factory
{
    protected $model = TipoAtivo::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        $unidades = ['unidade', 'metro', 'm2', 'kg'];

        return [
            'locador_id' => Pessoa::factory()->locador(),
            'nome' => fake()->unique()->words(3, true),
            'descricao' => fake()->sentence(),
            'unidade_medida' => fake()->randomElement($unidades),
            'valor_mensal_sugerido' => fake()->randomFloat(2, 2, 100),
        ];
    }

    /**
     * Tipo ativo especifico: Placa de EVA.
     */
    public function placaEva(): static
    {
        return $this->state(fn (array $attributes) => [
            'nome' => 'Placa de EVA',
            'descricao' => 'Placa de EVA para protecao de piso',
            'unidade_medida' => 'unidade',
            'valor_mensal_sugerido' => 5.00,
        ]);
    }

    /**
     * Tipo ativo especifico: Betoneira.
     */
    public function betoneira(): static
    {
        return $this->state(fn (array $attributes) => [
            'nome' => 'Betoneira 400L',
            'descricao' => 'Betoneira com capacidade de 400 litros',
            'unidade_medida' => 'unidade',
            'valor_mensal_sugerido' => 80.00,
        ]);
    }
}
