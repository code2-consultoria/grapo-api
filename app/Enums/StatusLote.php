<?php

namespace App\Enums;

enum StatusLote: string
{
    case Disponivel = 'disponivel';
    case Indisponivel = 'indisponivel';
    case Baixado = 'baixado';

    public function label(): string
    {
        return match ($this) {
            self::Disponivel => 'Disponível',
            self::Indisponivel => 'Indisponível',
            self::Baixado => 'Baixado',
        };
    }
}
