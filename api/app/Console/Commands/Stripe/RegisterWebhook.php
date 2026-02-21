<?php

namespace App\Console\Commands\Stripe;

use Illuminate\Console\Command;
use Stripe\StripeClient;

class RegisterWebhook extends Command
{
    protected $signature = 'stripe:webhook
                            {--url= : URL do webhook (usa APP_URL/api/stripe/webhook se não informado)}
                            {--delete : Remove webhooks existentes antes de criar}';

    protected $description = 'Registra webhook do Stripe e armazena o secret no .env';

    protected array $events = [
        'account.updated',
        'checkout.session.completed',
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'invoice.paid',
        'invoice.payment_failed',
    ];

    public function handle(): int
    {
        $stripe = new StripeClient(config('cashier.secret'));

        $url = $this->option('url') ?? config('app.url').'/api/stripe/webhook';

        $this->info("Registrando webhook para: {$url}");

        // Remove webhooks existentes se solicitado
        if ($this->option('delete')) {
            $this->deleteExistingWebhooks($stripe, $url);
        }

        // Verifica se já existe webhook para esta URL
        $existingWebhook = $this->findExistingWebhook($stripe, $url);
        if ($existingWebhook) {
            $this->warn("Webhook já existe para esta URL: {$existingWebhook->id}");

            if (! $this->confirm('Deseja criar um novo webhook?', false)) {
                return Command::SUCCESS;
            }
        }

        // Cria o webhook
        try {
            $webhook = $stripe->webhookEndpoints->create([
                'url' => $url,
                'enabled_events' => $this->events,
                'api_version' => '2024-12-18.acacia',
            ]);

            $this->info("Webhook criado: {$webhook->id}");
            $this->newLine();

            // Exibe o secret
            $this->warn('IMPORTANTE: Salve o webhook secret no seu .env:');
            $this->newLine();
            $this->line("STRIPE_WEBHOOK_SECRET={$webhook->secret}");
            $this->newLine();

            // Tenta atualizar o .env automaticamente
            if ($this->updateEnvFile($webhook->secret)) {
                $this->info('✓ .env atualizado automaticamente');
            } else {
                $this->warn('Não foi possível atualizar o .env automaticamente.');
                $this->warn('Adicione a linha acima ao seu arquivo .env');
            }

            $this->newLine();
            $this->info('Eventos registrados:');
            foreach ($this->events as $event) {
                $this->line("  - {$event}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erro ao criar webhook: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    protected function deleteExistingWebhooks(StripeClient $stripe, string $url): void
    {
        $webhooks = $stripe->webhookEndpoints->all(['limit' => 100]);

        foreach ($webhooks->data as $webhook) {
            if ($webhook->url === $url) {
                $stripe->webhookEndpoints->delete($webhook->id);
                $this->info("Webhook removido: {$webhook->id}");
            }
        }
    }

    protected function findExistingWebhook(StripeClient $stripe, string $url): ?\Stripe\WebhookEndpoint
    {
        $webhooks = $stripe->webhookEndpoints->all(['limit' => 100]);

        foreach ($webhooks->data as $webhook) {
            if ($webhook->url === $url) {
                return $webhook;
            }
        }

        return null;
    }

    protected function updateEnvFile(string $secret): bool
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);

        // Verifica se já existe a variável
        if (preg_match('/^STRIPE_WEBHOOK_SECRET=.*/m', $envContent)) {
            // Atualiza o valor existente
            $envContent = preg_replace(
                '/^STRIPE_WEBHOOK_SECRET=.*/m',
                "STRIPE_WEBHOOK_SECRET={$secret}",
                $envContent
            );
        } else {
            // Adiciona ao final
            $envContent .= "\nSTRIPE_WEBHOOK_SECRET={$secret}\n";
        }

        return file_put_contents($envPath, $envContent) !== false;
    }
}
