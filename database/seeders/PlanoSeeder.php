<?php

namespace Database\Seeders;

use App\Models\Plano;
use Illuminate\Database\Seeder;

class PlanoSeeder extends Seeder
{
    /**
     * Executa o seeder.
     */
    public function run(): void
    {
        $planos = [
            [
                'nome' => 'Trimestral',
                'duracao_meses' => 3,
                'valor' => 60.00,
                'ativo' => true,
            ],
            [
                'nome' => 'Semestral',
                'duracao_meses' => 6,
                'valor' => 110.00,
                'ativo' => true,
            ],
            [
                'nome' => 'Anual',
                'duracao_meses' => 12,
                'valor' => 200.00,
                'ativo' => true,
            ],
        ];

        foreach ($planos as $plano) {
            Plano::updateOrCreate(
                ['nome' => $plano['nome']],
                $plano
            );
        }
    }
}
