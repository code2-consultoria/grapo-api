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
        Schema::create('contrato_itens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->foreignUuid('tipo_ativo_id')->constrained('tipos_ativos');
            $table->integer('quantidade');
            $table->decimal('valor_unitario_diaria', 10, 2);
            $table->decimal('valor_total_item', 12, 2);
            $table->timestamps();

            $table->index('contrato_id');
            $table->index('tipo_ativo_id');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_itens');
    }
};
