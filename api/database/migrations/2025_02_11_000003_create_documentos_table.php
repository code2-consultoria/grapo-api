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
        Schema::create('documentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pessoa_id')->constrained('pessoas')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('numero');
            $table->timestamps();

            $table->index('pessoa_id');
            $table->index('tipo');
            $table->unique(['pessoa_id', 'tipo']);
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
