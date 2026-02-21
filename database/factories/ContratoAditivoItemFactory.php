<?php

namespace Database\Factories;

use App\Models\ContratoAditivo;
use App\Models\ContratoAditivoItem;
use App\Models\TipoAtivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContratoAditivoItem>
 */
class ContratoAditivoItemFactory extends Factory
{
    protected $model = ContratoAditivoItem::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        return [
            'contrato_aditivo_id' => ContratoAditivo::factory(),
            'tipo_ativo_id' => TipoAtivo::factory(),
            'quantidade_alterada' => fake()->numberBetween(1, 10),
            'valor_unitario' => fake()->randomFloat(2, 10, 100),
        ];
    }

    /**
     * Item de acrescimo (quantidade positiva).
     */
    public function acrescimo(?int $quantidade = null): static
    {
        return $this->state(fn () => [
            'quantidade_alterada' => $quantidade ?? fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Item de reducao (quantidade negativa).
     */
    public function reducao(?int $quantidade = null): static
    {
        $qtd = $quantidade ?? fake()->numberBetween(1, 5);

        return $this->state(fn () => [
            'quantidade_alterada' => -abs($qtd),
        ]);
    }

    /**
     * Item com quantidade especifica.
     */
    public function comQuantidade(int $quantidade): static
    {
        return $this->state(fn () => [
            'quantidade_alterada' => $quantidade,
        ]);
    }

    /**
     * Item com valor unitario especifico.
     */
    public function comValorUnitario(float $valor): static
    {
        return $this->state(fn () => [
            'valor_unitario' => $valor,
        ]);
    }
}
