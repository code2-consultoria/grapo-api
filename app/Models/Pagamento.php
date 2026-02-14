<?php

namespace App\Models;

use App\Enums\OrigemPagamento;
use App\Enums\StatusPagamento;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'valor',
        'desconto_comercial',
        'data_vencimento',
        'data_pagamento',
        'status',
        'origem',
        'stripe_payment_id',
        'stripe_invoice_id',
        'observacoes',
    ];

    protected $appends = ['valor_final'];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'desconto_comercial' => 'decimal:2',
            'data_vencimento' => 'date',
            'data_pagamento' => 'date',
            'status' => StatusPagamento::class,
            'origem' => OrigemPagamento::class,
        ];
    }

    public function getValorFinalAttribute(): float
    {
        return (float) $this->valor - (float) $this->desconto_comercial;
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function isPago(): bool
    {
        return $this->status === StatusPagamento::Pago;
    }

    public function isPendente(): bool
    {
        return $this->status === StatusPagamento::Pendente;
    }

    public function isAtrasado(): bool
    {
        return $this->status === StatusPagamento::Atrasado;
    }

    public function marcarComoPago(?string $stripePaymentId = null): void
    {
        $this->update([
            'status' => StatusPagamento::Pago,
            'data_pagamento' => now(),
            'stripe_payment_id' => $stripePaymentId ?? $this->stripe_payment_id,
        ]);
    }

    public function marcarComoAtrasado(): void
    {
        $this->update([
            'status' => StatusPagamento::Atrasado,
        ]);
    }

    public function cancelar(): void
    {
        $this->update([
            'status' => StatusPagamento::Cancelado,
        ]);
    }
}
