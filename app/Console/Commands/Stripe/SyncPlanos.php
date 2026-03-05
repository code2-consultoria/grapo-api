<?php

namespace App\Console\Commands\Stripe;

use App\Models\Plano;
use Illuminate\Console\Command;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class SyncPlanos extends Command
{
    protected $signature = 'stripe:sync-planos';

    protected $description = 'Sincroniza os planos do banco com o Stripe (cria Products e Prices)';

    public function handle(): int
    {
        Stripe::setApiKey(config('cashier.secret'));

        $planos = Plano::query()->whereNull('stripe_price_id')->get();

        if ($planos->isEmpty()) {
            $this->info('Todos os planos ja possuem stripe_price_id.');

            return self::SUCCESS;
        }

        $this->info("Sincronizando {$planos->count()} plano(s) com o Stripe...");

        foreach ($planos as $plano) {
            $this->syncPlano($plano);
        }

        $this->info('Sincronizacao concluida.');

        return self::SUCCESS;
    }

    private function syncPlano(Plano $plano): void
    {
        $this->line("Processando: {$plano->nome} (R$ {$plano->valor})");

        // Cria o Product no Stripe
        $product = Product::create([
            'name' => "Grapo - Plano {$plano->nome}",
            'description' => "Assinatura {$plano->nome} ({$plano->duracao_meses} meses)",
            'metadata' => [
                'plano_id' => $plano->id,
                'duracao_meses' => $plano->duracao_meses,
            ],
        ]);

        // Cria o Price no Stripe (valor em centavos)
        $price = Price::create([
            'product' => $product->id,
            'unit_amount' => (int) ($plano->valor * 100),
            'currency' => 'brl',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => $plano->duracao_meses,
            ],
            'metadata' => [
                'plano_id' => $plano->id,
            ],
        ]);

        // Atualiza o plano no banco
        $plano->update(['stripe_price_id' => $price->id]);

        $this->info("  -> Criado: {$price->id}");
    }
}
