<?php

namespace App\Enums;

enum StatusPagamento: string
{
    case Pendente = 'pendente';
    case Pago = 'pago';
    case Atrasado = 'atrasado';
    case Cancelado = 'cancelado';
    case Reembolsado = 'reembolsado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Pago => 'Pago',
            self::Atrasado => 'Atrasado',
            self::Cancelado => 'Cancelado',
            self::Reembolsado => 'Reembolsado',
        };
    }

    public function isPago(): bool
    {
        return $this === self::Pago;
    }

    public function isPendente(): bool
    {
        return $this === self::Pendente || $this === self::Atrasado;
    }
}
