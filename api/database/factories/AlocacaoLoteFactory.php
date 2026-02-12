<?php

namespace Database\Factories;

use App\Models\AlocacaoLote;
use App\Models\ContratoItem;
use App\Models\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlocacaoLote>
 */
class AlocacaoLoteFactory extends Factory
{
    protected $model = AlocacaoLote::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        return [
            'contrato_item_id' => ContratoItem::factory(),
            'lote_id' => Lote::factory(),
            'quantidade_alocada' => fake()->numberBetween(1, 10),
        ];
    }

    /**
     * Alocacao com quantidade especifica.
     */
    public function comQuantidade(int $quantidade): static
    {
        return $this->state(fn (array $attributes) => [
            'quantidade_alocada' => $quantidade,
        ]);
    }
}
