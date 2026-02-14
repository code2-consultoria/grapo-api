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
        Schema::table('pessoas', function (Blueprint $table) {
            // Stripe Connect Express para locadores receberem pagamentos
            $table->string('stripe_account_id')->nullable()->after('trial_ends_at');
            $table->boolean('stripe_connect_onboarding_complete')->default(false)->after('stripe_account_id');
            $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_connect_onboarding_complete');
            $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_charges_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_account_id',
                'stripe_connect_onboarding_complete',
                'stripe_charges_enabled',
                'stripe_payouts_enabled',
            ]);
        });
    }
};
