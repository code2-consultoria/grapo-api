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
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn('valor_unitario_diaria');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->string('fornecedor')->nullable()->after('status');
            $table->decimal('valor_total', 10, 2)->nullable()->after('fornecedor');
            $table->decimal('valor_frete', 10, 2)->nullable()->after('valor_total');
            $table->string('forma_pagamento')->nullable()->after('valor_frete');
            $table->string('nf')->nullable()->after('forma_pagamento');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn(['fornecedor', 'valor_total', 'valor_frete', 'forma_pagamento', 'nf']);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->decimal('valor_unitario_diaria', 10, 2)->after('quantidade_disponivel');
        });
    }
};
