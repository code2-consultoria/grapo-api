<?php

namespace Database\Factories;

use App\Models\Assinatura;
use App\Models\Pessoa;
use App\Models\Plano;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assinatura>
 */
class AssinaturaFactory extends Factory
{
    protected $model = Assinatura::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        $dataInicio = fake()->dateTimeBetween('-1 month', 'now');
        $duracaoMeses = fake()->randomElement([3, 6, 12]);

        return [
            'locador_id' => Pessoa::factory()->locador(),
            'plano_id' => Plano::factory(),
            'data_inicio' => $dataInicio,
            'data_termino' => (clone $dataInicio)->modify("+{$duracaoMeses} months"),
            'status' => 'ativa',
        ];
    }

    /**
     * Assinatura ativa.
     */
    public function ativa(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ativa',
            'data_inicio' => now()->subMonth(),
            'data_termino' => now()->addMonths(2),
        ]);
    }

    /**
     * Assinatura expirada.
     */
    public function expirada(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expirada',
            'data_inicio' => now()->subMonths(4),
            'data_termino' => now()->subMonth(),
        ]);
    }

    /**
     * Assinatura cancelada.
     */
    public function cancelada(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelada',
        ]);
    }
}
