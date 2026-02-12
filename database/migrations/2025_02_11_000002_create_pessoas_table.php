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
        // Criar tabela primeiro
        Schema::create('pessoas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('locador_id')->nullable();
            $table->string('tipo');
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->text('endereco')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('tipo');
            $table->index('locador_id');
        });

        // Adicionar FK auto-referencial depois
        Schema::table('pessoas', function (Blueprint $table) {
            $table->foreign('locador_id')->references('id')->on('pessoas');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
