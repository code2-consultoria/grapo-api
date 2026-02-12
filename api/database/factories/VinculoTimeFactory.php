<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\User;
use App\Models\VinculoTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VinculoTime>
 */
class VinculoTimeFactory extends Factory
{
    protected $model = VinculoTime::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'locador_id' => Pessoa::factory()->locador(),
        ];
    }
}
