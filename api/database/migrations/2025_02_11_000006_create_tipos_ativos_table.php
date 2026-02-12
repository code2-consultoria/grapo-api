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
        Schema::create('tipos_ativos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('locador_id')->constrained('pessoas')->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('unidade_medida')->default('unidade');
            $table->decimal('valor_diaria_sugerido', 10, 2)->nullable();
            $table->timestamps();

            $table->index('locador_id');
            $table->unique(['locador_id', 'nome']);
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_ativos');
    }
};
