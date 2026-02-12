<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar planos padrao
        $this->call(PlanoSeeder::class);

        // Criar usuario de teste
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'papel' => 'admin',
        ]);
    }
}
