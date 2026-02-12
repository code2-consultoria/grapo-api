<?php

namespace Database\Factories;

use App\Enums\TipoDocumento;
use App\Models\Documento;
use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documento>
 */
class DocumentoFactory extends Factory
{
    protected $model = Documento::class;

    /**
     * Define o estado padrao do model.
     */
    public function definition(): array
    {
        return [
            'pessoa_id' => Pessoa::factory(),
            'tipo' => TipoDocumento::Cpf,
            'numero' => fake()->numerify('###########'),
        ];
    }

    /**
     * Documento do tipo CPF.
     */
    public function cpf(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::Cpf,
            'numero' => fake()->numerify('###########'),
        ]);
    }

    /**
     * Documento do tipo CNPJ.
     */
    public function cnpj(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::Cnpj,
            'numero' => fake()->numerify('##############'),
        ]);
    }

    /**
     * Documento do tipo RG.
     */
    public function rg(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::Rg,
            'numero' => fake()->numerify('#########'),
        ]);
    }

    /**
     * Documento do tipo CNH.
     */
    public function cnh(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::Cnh,
            'numero' => fake()->numerify('###########'),
        ]);
    }

    /**
     * Documento do tipo Passaporte.
     */
    public function passaporte(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::Passaporte,
            'numero' => fake()->regexify('[A-Z]{2}[0-9]{6}'),
        ]);
    }

    /**
     * Documento do tipo Inscrição Municipal.
     */
    public function inscricaoMunicipal(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::InscricaoMunicipal,
            'numero' => fake()->numerify('#########'),
        ]);
    }

    /**
     * Documento do tipo Inscrição Estadual.
     */
    public function inscricaoEstadual(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::InscricaoEstadual,
            'numero' => fake()->numerify('############'),
        ]);
    }

    /**
     * Documento do tipo CadÚnico.
     */
    public function cadUnico(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoDocumento::CadUnico,
            'numero' => fake()->numerify('###########'),
        ]);
    }
}
