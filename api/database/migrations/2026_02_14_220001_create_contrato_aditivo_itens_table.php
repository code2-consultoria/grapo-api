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
        Schema::create('contrato_aditivo_itens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contrato_aditivo_id')
                ->constrained('contrato_aditivos')
                ->onDelete('cascade');
            $table->foreignUuid('tipo_ativo_id')->constrained('tipos_ativos');
            $table->integer('quantidade_alterada'); // positivo=acréscimo, negativo=redução
            $table->decimal('valor_unitario', 12, 2)->nullable();
            $table->timestamps();

            $table->index('contrato_aditivo_id');
            $table->index('tipo_ativo_id');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_aditivo_itens');
    }
};
