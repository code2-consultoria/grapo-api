<?php

namespace App\Enums;

enum StatusAditivo: string
{
    case Rascunho = 'rascunho';
    case Ativo = 'ativo';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Ativo => 'Ativo',
            self::Cancelado => 'Cancelado',
        };
    }

    public function podeSerEditado(): bool
    {
        return $this === self::Rascunho;
    }

    public function podeSerAtivado(): bool
    {
        return $this === self::Rascunho;
    }

    public function podeSerCancelado(): bool
    {
        return $this === self::Rascunho || $this === self::Ativo;
    }
}
