<?php

namespace Database\Factories;

use App\Models\Contrato;
use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contrato>
 */
class ContratoFactory extends Factory
{
    protected $model = Contrato::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        $dataInicio = fake()->dateTimeBetween('now', '+1 month');
        $dataTermino = (clone $dataInicio)->modify('+' . fake()->numberBetween(1, 6) . ' months');

        return [
            'locador_id' => Pessoa::factory()->locador(),
            'locatario_id' => Pessoa::factory()->locatario(),
            'codigo' => 'CTR-' . fake()->unique()->numerify('####'),
            'data_inicio' => $dataInicio,
            'data_termino' => $dataTermino,
            'valor_total' => 0,
            'status' => 'rascunho',
            'observacoes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Contrato em rascunho.
     */
    public function rascunho(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rascunho',
        ]);
    }

    /**
     * Contrato ativo.
     */
    public function ativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ativo',
        ]);
    }

    /**
     * Contrato finalizado.
     */
    public function finalizado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finalizado',
            'data_inicio' => now()->subMonths(3),
            'data_termino' => now()->subMonth(),
        ]);
    }

    /**
     * Contrato cancelado.
     */
    public function cancelado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelado',
        ]);
    }

    /**
     * Contrato com periodo especifico.
     */
    public function comPeriodo(\DateTime $inicio, \DateTime $termino): static
    {
        return $this->state(fn (array $attributes) => [
            'data_inicio' => $inicio,
            'data_termino' => $termino,
        ]);
    }

    /**
     * Contrato com valor total.
     */
    public function comValor(float $valor): static
    {
        return $this->state(fn (array $attributes) => [
            'valor_total' => $valor,
        ]);
    }
}
