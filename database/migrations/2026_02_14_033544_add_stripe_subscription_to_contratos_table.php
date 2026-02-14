<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Stripe Connect - Assinatura recorrente do contrato
            $table->string('stripe_subscription_id')->nullable()->after('status');
            $table->string('stripe_customer_id')->nullable()->after('stripe_subscription_id');
            $table->integer('dia_vencimento')->nullable()->after('stripe_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_subscription_id',
                'stripe_customer_id',
                'dia_vencimento',
            ]);
        });
    }
};
