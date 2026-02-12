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
        Schema::create('lotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('locador_id')->constrained('pessoas')->cascadeOnDelete();
            $table->foreignUuid('tipo_ativo_id')->constrained('tipos_ativos');
            $table->string('codigo');
            $table->integer('quantidade_total');
            $table->integer('quantidade_disponivel');
            $table->decimal('valor_unitario_diaria', 10, 2);
            $table->decimal('custo_aquisicao', 10, 2)->nullable();
            $table->date('data_aquisicao')->nullable();
            $table->string('status')->default('disponivel');
            $table->timestamps();

            $table->index('locador_id');
            $table->index('tipo_ativo_id');
            $table->index('status');
            $table->unique(['locador_id', 'codigo']);
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
