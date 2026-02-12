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
        Schema::create('contratos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('locador_id')->constrained('pessoas');
            $table->foreignUuid('locatario_id')->constrained('pessoas');
            $table->string('codigo');
            $table->date('data_inicio');
            $table->date('data_termino');
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->string('status')->default('rascunho');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('locador_id');
            $table->index('locatario_id');
            $table->index('status');
            $table->unique(['locador_id', 'codigo']);
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
