<?php

namespace App\Actions\Contrato\Aditivo\Stripe;

use App\Contracts\Command;
use App\Models\Contrato;
use App\Models\ContratoAditivo;
use Stripe\StripeClient;

/**
 * Cria invoice item proporcional para acréscimo ou crédito para redução.
 */
class CriarCobrancaProporcional implements Command
{
    private ?string $invoiceItemId = null;

    public function __construct(
        private Contrato $contrato,
        private ContratoAditivo $aditivo,
        private float $valorMensalAlteracao,
        private bool $isCredito = false
    ) {}

    /**
     * Executa a criação da cobrança proporcional.
     */
    public function handle(): void
    {
        // Verifica se contrato tem subscription
        if (! $this->contrato->stripe_subscription_id || ! $this->contrato->stripe_customer_id) {
            return;
        }

        $locador = $this->contrato->locador;
        $connectConfig = $locador->stripeConnect();

        if (! $connectConfig->isReady()) {
            return;
        }

        // Calcula dias restantes até próxima fatura
        $diasRestantes = $this->calcularDiasRestantes();

        if ($diasRestantes <= 0) {
            return;
        }

        // Calcula valor proporcional (sem majoração de diária)
        $valorProporcional = ($this->valorMensalAlteracao * $diasRestantes) / 30;

        if ($valorProporcional == 0) {
            return;
        }

        $stripe = new StripeClient(config('cashier.secret'));

        // Se for crédito (redução com reembolso), valor negativo
        $amount = (int) ($valorProporcional * 100);
        if ($this->isCredito) {
            $amount = -abs($amount);
        }

        // Descrição da cobrança
        $descricao = $this->isCredito
            ? "Aditivo #{$this->aditivo->id} - Crédito proporcional ({$diasRestantes} dias)"
            : "Aditivo #{$this->aditivo->id} - Cobrança proporcional ({$diasRestantes} dias)";

        // Cria invoice item
        $invoiceItem = $stripe->invoiceItems->create([
            'customer' => $this->contrato->stripe_customer_id,
            'amount' => $amount,
            'currency' => 'brl',
            'description' => $descricao,
        ], ['stripe_account' => $connectConfig->accountId]);

        $this->invoiceItemId = $invoiceItem->id;

        // Guarda para possível cancelamento
        $this->aditivo->stripe_invoice_item_id = $invoiceItem->id;
        $this->aditivo->save();
    }

    /**
     * Calcula dias restantes até a próxima fatura.
     */
    private function calcularDiasRestantes(): int
    {
        $diaVencimento = $this->contrato->dia_vencimento ?? 1;
        $hoje = now();
        $proximoVencimento = $hoje->copy()->day($diaVencimento);

        if ($proximoVencimento->lte($hoje)) {
            $proximoVencimento->addMonth();
        }

        return $hoje->diffInDays($proximoVencimento);
    }

    public function getInvoiceItemId(): ?string
    {
        return $this->invoiceItemId;
    }
}
