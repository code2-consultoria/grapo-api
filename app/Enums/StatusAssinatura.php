<?php

namespace App\Enums;

enum StatusAssinatura: string
{
    case Ativa = 'ativa';
    case Expirada = 'expirada';
    case Cancelada = 'cancelada';
    case Pendente = 'pendente';

    public function label(): string
    {
        return match ($this) {
            self::Ativa => 'Ativa',
            self::Expirada => 'Expirada',
            self::Cancelada => 'Cancelada',
            self::Pendente => 'Pendente',
        };
    }
}
