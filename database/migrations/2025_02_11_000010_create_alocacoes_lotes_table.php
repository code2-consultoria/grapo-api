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
        Schema::create('alocacoes_lotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contrato_item_id')->constrained('contrato_itens')->cascadeOnDelete();
            $table->foreignUuid('lote_id')->constrained('lotes');
            $table->integer('quantidade_alocada');
            $table->timestamps();

            $table->index('contrato_item_id');
            $table->index('lote_id');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('alocacoes_lotes');
    }
};
