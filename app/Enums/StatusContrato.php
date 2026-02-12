<?php

namespace App\Enums;

enum StatusContrato: string
{
    case Rascunho = 'rascunho';
    case Ativo = 'ativo';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Ativo => 'Ativo',
            self::Finalizado => 'Finalizado',
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
        return $this === self::Ativo;
    }

    public function podeSerFinalizado(): bool
    {
        return $this === self::Ativo;
    }
}
