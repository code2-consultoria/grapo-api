<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolida campos do Stripe Connect em um campo JSON.
     * Mantém os campos do Cashier (stripe_id, pm_type, etc) para compatibilidade.
     */
    public function up(): void
    {
        // 1. Adiciona coluna JSON para Connect config
        Schema::table('pessoas', function (Blueprint $table) {
            $table->json('stripe_connect_config')->nullable()->after('trial_ends_at');
        });

        // 2. Migra dados existentes para JSON
        DB::table('pessoas')
            ->whereNotNull('stripe_account_id')
            ->orderBy('id')
            ->chunk(100, function ($pessoas) {
                foreach ($pessoas as $pessoa) {
                    $config = [
                        'account_id' => $pessoa->stripe_account_id,
                        'onboarding_complete' => (bool) $pessoa->stripe_connect_onboarding_complete,
                        'charges_enabled' => (bool) $pessoa->stripe_charges_enabled,
                        'payouts_enabled' => (bool) $pessoa->stripe_payouts_enabled,
                        'webhook_endpoint_id' => null,
                        'webhook_secret' => null,
                    ];

                    DB::table('pessoas')
                        ->where('id', $pessoa->id)
                        ->update(['stripe_connect_config' => json_encode($config)]);
                }
            });

        // 3. Remove colunas antigas do Connect
        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_account_id',
                'stripe_connect_onboarding_complete',
                'stripe_charges_enabled',
                'stripe_payouts_enabled',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recria colunas antigas
        Schema::table('pessoas', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable()->after('trial_ends_at');
            $table->boolean('stripe_connect_onboarding_complete')->default(false)->after('stripe_account_id');
            $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_connect_onboarding_complete');
            $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_charges_enabled');
        });

        // 2. Migra dados de volta
        DB::table('pessoas')
            ->whereNotNull('stripe_connect_config')
            ->orderBy('id')
            ->chunk(100, function ($pessoas) {
                foreach ($pessoas as $pessoa) {
                    $config = json_decode($pessoa->stripe_connect_config, true);

                    DB::table('pessoas')
                        ->where('id', $pessoa->id)
                        ->update([
                            'stripe_account_id' => $config['account_id'] ?? null,
                            'stripe_connect_onboarding_complete' => $config['onboarding_complete'] ?? false,
                            'stripe_charges_enabled' => $config['charges_enabled'] ?? false,
                            'stripe_payouts_enabled' => $config['payouts_enabled'] ?? false,
                        ]);
                }
            });

        // 3. Remove coluna JSON
        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropColumn('stripe_connect_config');
        });
    }
};
