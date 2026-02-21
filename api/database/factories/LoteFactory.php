<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lote>
 */
class LoteFactory extends Factory
{
    protected $model = Lote::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        $quantidade = fake()->numberBetween(5, 50);
        $valorTotal = fake()->randomFloat(2, 100, 5000);
        $valorFrete = fake()->randomFloat(2, 0, 500);

        return [
            'locador_id' => Pessoa::factory()->locador(),
            'tipo_ativo_id' => TipoAtivo::factory(),
            'codigo' => 'LOT-'.fake()->unique()->numerify('####'),
            'quantidade_total' => $quantidade,
            'quantidade_disponivel' => $quantidade,
            'fornecedor' => fake()->company(),
            'valor_total' => $valorTotal,
            'valor_frete' => $valorFrete,
            'forma_pagamento' => fake()->randomElement(['pix', 'boleto', 'cartao', 'transferencia']),
            'nf' => fake()->numerify('NF-######'),
            'custo_aquisicao' => $valorTotal + $valorFrete,
            'data_aquisicao' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => 'disponivel',
        ];
    }

    /**
     * Lote com quantidade especifica.
     */
    public function comQuantidade(int $quantidade): static
    {
        return $this->state(fn (array $attributes) => [
            'quantidade_total' => $quantidade,
            'quantidade_disponivel' => $quantidade,
        ]);
    }

    /**
     * Lote parcialmente alocado.
     */
    public function parcialmenteAlocado(int $total, int $disponivel): static
    {
        return $this->state(fn (array $attributes) => [
            'quantidade_total' => $total,
            'quantidade_disponivel' => $disponivel,
        ]);
    }

    /**
     * Lote totalmente alocado (sem disponibilidade).
     */
    public function semDisponibilidade(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantidade_disponivel' => 0,
        ]);
    }

    /**
     * Lote indisponivel.
     */
    public function indisponivel(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'indisponivel',
        ]);
    }

    /**
     * Lote antigo (para testes de FIFO).
     */
    public function antigo(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_aquisicao' => fake()->dateTimeBetween('-2 years', '-1 year'),
            'created_at' => fake()->dateTimeBetween('-2 years', '-1 year'),
        ]);
    }

    /**
     * Lote recente (para testes de FIFO).
     */
    public function recente(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_aquisicao' => fake()->dateTimeBetween('-1 month', 'now'),
            'created_at' => now(),
        ]);
    }
}
