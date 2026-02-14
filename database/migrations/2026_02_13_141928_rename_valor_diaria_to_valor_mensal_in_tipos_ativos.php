<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration.
     */
    public function up(): void
    {
        Schema::table('tipos_ativos', function (Blueprint $table) {
            $table->renameColumn('valor_diaria_sugerido', 'valor_mensal_sugerido');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::table('tipos_ativos', function (Blueprint $table) {
            $table->renameColumn('valor_mensal_sugerido', 'valor_diaria_sugerido');
        });
    }
};
