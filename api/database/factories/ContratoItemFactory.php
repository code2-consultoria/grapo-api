<?php

namespace Database\Factories;

use App\Models\ContratoItem;
use App\Models\Contrato;
use App\Models\TipoAtivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContratoItem>
 */
class ContratoItemFactory extends Factory
{
    protected $model = ContratoItem::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        $quantidade = fake()->numberBetween(1, 20);
        $valorUnitario = fake()->randomFloat(2, 2, 100);
        $diasLocacao = 30; // valor padrao para calculo

        return [
            'contrato_id' => Contrato::factory(),
            'tipo_ativo_id' => TipoAtivo::factory(),
            'quantidade' => $quantidade,
            'valor_unitario_diaria' => $valorUnitario,
            'valor_total_item' => $quantidade * $valorUnitario * $diasLocacao,
        ];
    }

    /**
     * Item com quantidade especifica.
     */
    public function comQuantidade(int $quantidade): static
    {
        return $this->state(fn (array $attributes) => [
            'quantidade' => $quantidade,
            'valor_total_item' => $quantidade * $attributes['valor_unitario_diaria'] * 30,
        ]);
    }

    /**
     * Item com valor unitario especifico.
     */
    public function comValorUnitario(float $valor): static
    {
        return $this->state(fn (array $attributes) => [
            'valor_unitario_diaria' => $valor,
            'valor_total_item' => $attributes['quantidade'] * $valor * 30,
        ]);
    }
}
