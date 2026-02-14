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
        Schema::table('contrato_itens', function (Blueprint $table) {
            // Renomeia valor_unitario_diaria para valor_unitario
            $table->renameColumn('valor_unitario_diaria', 'valor_unitario');
        });

        Schema::table('contrato_itens', function (Blueprint $table) {
            // Adiciona campo periodo_aluguel (diaria ou mensal)
            $table->string('periodo_aluguel', 10)->default('diaria')->after('valor_unitario');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::table('contrato_itens', function (Blueprint $table) {
            $table->dropColumn('periodo_aluguel');
        });

        Schema::table('contrato_itens', function (Blueprint $table) {
            $table->renameColumn('valor_unitario', 'valor_unitario_diaria');
        });
    }
};
