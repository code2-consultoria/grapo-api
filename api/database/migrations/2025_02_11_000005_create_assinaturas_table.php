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
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('locador_id')->constrained('pessoas')->cascadeOnDelete();
            $table->foreignUuid('plano_id')->constrained('planos');
            $table->date('data_inicio');
            $table->date('data_termino');
            $table->string('status')->default('ativa');
            $table->timestamps();

            $table->index('locador_id');
            $table->index('status');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('assinaturas');
    }
};
