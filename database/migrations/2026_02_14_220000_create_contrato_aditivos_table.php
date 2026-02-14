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
        Schema::create('contrato_aditivos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->string('tipo'); // prorrogacao, acrescimo, reducao, alteracao_valor
            $table->text('descricao')->nullable();
            $table->date('data_vigencia');
            $table->decimal('valor_ajuste', 12, 2)->nullable(); // para alteracao_valor
            $table->date('nova_data_termino')->nullable(); // para prorrogacao
            $table->boolean('conceder_reembolso')->default(false); // para reducao com Stripe
            $table->string('status')->default('rascunho'); // rascunho, ativo, cancelado

            // Campos para integração Stripe
            $table->string('stripe_price_anterior_id')->nullable(); // para reversão
            $table->string('stripe_invoice_item_id')->nullable(); // para cancelamento

            // Campos para auditoria de reversão
            $table->date('data_termino_anterior')->nullable(); // para reverter prorrogação
            $table->decimal('valor_total_anterior', 12, 2)->nullable(); // para reverter alteração de valor

            $table->timestamps();
            $table->softDeletes();

            $table->index('contrato_id');
            $table->index('status');
            $table->index('tipo');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_aditivos');
    }
};
