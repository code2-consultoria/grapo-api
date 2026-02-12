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
        Schema::create('planos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->integer('duracao_meses');
            $table->decimal('valor', 10, 2);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('planos');
    }
};
