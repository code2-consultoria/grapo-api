<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Value Object para configurações Stripe Connect de um locador.
 * Campos do Cashier (stripe_id, pm_type, etc) são mantidos separados para compatibilidade.
 */
final class StripeConnectConfig implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly ?string $accountId = null,
        public readonly bool $onboardingComplete = false,
        public readonly bool $chargesEnabled = false,
        public readonly bool $payoutsEnabled = false,
        public readonly ?string $webhookEndpointId = null,
        public readonly ?string $webhookSecret = null,
    ) {}

    public static function fromArray(?array $data): self
    {
        if ($data === null) {
            return new self();
        }

        return new self(
            accountId: $data['account_id'] ?? null,
            onboardingComplete: $data['onboarding_complete'] ?? false,
            chargesEnabled: $data['charges_enabled'] ?? false,
            payoutsEnabled: $data['payouts_enabled'] ?? false,
            webhookEndpointId: $data['webhook_endpoint_id'] ?? null,
            webhookSecret: $data['webhook_secret'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'onboarding_complete' => $this->onboardingComplete,
            'charges_enabled' => $this->chargesEnabled,
            'payouts_enabled' => $this->payoutsEnabled,
            'webhook_endpoint_id' => $this->webhookEndpointId,
            'webhook_secret' => $this->webhookSecret,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // Helpers

    public function hasAccount(): bool
    {
        return $this->accountId !== null;
    }

    public function isReady(): bool
    {
        return $this->hasAccount()
            && $this->onboardingComplete
            && $this->chargesEnabled;
    }

    public function hasWebhook(): bool
    {
        return $this->webhookEndpointId !== null && $this->webhookSecret !== null;
    }

    // Métodos para criar nova instância com valores alterados (imutabilidade)

    public function withAccount(string $accountId): self
    {
        return new self(
            accountId: $accountId,
            onboardingComplete: $this->onboardingComplete,
            chargesEnabled: $this->chargesEnabled,
            payoutsEnabled: $this->payoutsEnabled,
            webhookEndpointId: $this->webhookEndpointId,
            webhookSecret: $this->webhookSecret,
        );
    }

    public function withStatus(bool $onboardingComplete, bool $chargesEnabled, bool $payoutsEnabled): self
    {
        return new self(
            accountId: $this->accountId,
            onboardingComplete: $onboardingComplete,
            chargesEnabled: $chargesEnabled,
            payoutsEnabled: $payoutsEnabled,
            webhookEndpointId: $this->webhookEndpointId,
            webhookSecret: $this->webhookSecret,
        );
    }

    public function withWebhook(string $endpointId, string $secret): self
    {
        return new self(
            accountId: $this->accountId,
            onboardingComplete: $this->onboardingComplete,
            chargesEnabled: $this->chargesEnabled,
            payoutsEnabled: $this->payoutsEnabled,
            webhookEndpointId: $endpointId,
            webhookSecret: $secret,
        );
    }
}
