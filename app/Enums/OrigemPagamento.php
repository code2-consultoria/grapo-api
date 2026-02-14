<?php

namespace App\Enums;

enum OrigemPagamento: string
{
    case Stripe = 'stripe';
    case Pix = 'pix';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Cartão (Stripe)',
            self::Pix => 'PIX',
            self::Manual => 'Manual',
        };
    }
}
