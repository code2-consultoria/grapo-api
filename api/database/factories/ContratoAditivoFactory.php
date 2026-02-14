<?php

namespace Database\Factories;

use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use App\Models\Contrato;
use App\Models\ContratoAditivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContratoAditivo>
 */
class ContratoAditivoFactory extends Factory
{
    protected $model = ContratoAditivo::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'tipo' => TipoAditivo::Prorrogacao,
            'descricao' => fake()->sentence(),
            'data_vigencia' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => StatusAditivo::Rascunho,
        ];
    }

    /**
     * Aditivo de prorrogacao.
     */
    public function prorrogacao(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoAditivo::Prorrogacao,
            'nova_data_termino' => fake()->dateTimeBetween('+2 months', '+6 months'),
        ]);
    }

    /**
     * Aditivo de acrescimo.
     */
    public function acrescimo(): static
    {
        return $this->state(fn () => [
            'tipo' => TipoAditivo::Acrescimo,
        ]);
    }

    /**
     * Aditivo de reducao.
     */
    public function reducao(): static
    {
        return $this->state(fn () => [
            'tipo' => TipoAditivo::Reducao,
        ]);
    }

    /**
     * Aditivo de alteracao de valor.
     */
    public function alteracaoValor(float $valor = null): static
    {
        return $this->state(fn () => [
            'tipo' => TipoAditivo::AlteracaoValor,
            'valor_ajuste' => $valor ?? fake()->randomFloat(2, -500, 500),
        ]);
    }

    /**
     * Aditivo ativo (ja aplicado).
     */
    public function ativo(): static
    {
        return $this->state(fn () => [
            'status' => StatusAditivo::Ativo,
        ]);
    }

    /**
     * Aditivo cancelado.
     */
    public function cancelado(): static
    {
        return $this->state(fn () => [
            'status' => StatusAditivo::Cancelado,
        ]);
    }

    /**
     * Aditivo com reembolso (para reducao com Stripe).
     */
    public function comReembolso(): static
    {
        return $this->state(fn () => [
            'conceder_reembolso' => true,
        ]);
    }
}
