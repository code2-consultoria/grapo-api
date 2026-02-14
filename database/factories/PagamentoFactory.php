<?php

namespace Database\Factories;

use App\Enums\OrigemPagamento;
use App\Enums\StatusPagamento;
use App\Models\Contrato;
use App\Models\Pagamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pagamento>
 */
class PagamentoFactory extends Factory
{
    protected $model = Pagamento::class;

    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'valor' => $this->faker->randomFloat(2, 100, 5000),
            'desconto_comercial' => 0,
            'data_vencimento' => $this->faker->dateTimeBetween('now', '+30 days'),
            'data_pagamento' => null,
            'status' => StatusPagamento::Pendente,
            'origem' => $this->faker->randomElement(OrigemPagamento::cases()),
            'stripe_payment_id' => null,
            'stripe_invoice_id' => null,
            'observacoes' => null,
        ];
    }

    public function pago(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusPagamento::Pago,
            'data_pagamento' => now(),
        ]);
    }

    public function pendente(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusPagamento::Pendente,
            'data_pagamento' => null,
        ]);
    }

    public function atrasado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusPagamento::Atrasado,
            'data_vencimento' => now()->subDays(5),
            'data_pagamento' => null,
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'origem' => OrigemPagamento::Stripe,
        ]);
    }

    public function pix(): static
    {
        return $this->state(fn (array $attributes) => [
            'origem' => OrigemPagamento::Pix,
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'origem' => OrigemPagamento::Manual,
        ]);
    }
}
