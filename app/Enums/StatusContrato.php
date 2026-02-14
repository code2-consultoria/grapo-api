<?php

namespace App\Enums;

enum StatusContrato: string
{
    case Rascunho = 'rascunho';
    case AguardandoPagamento = 'aguardando_pagamento';
    case Ativo = 'ativo';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::AguardandoPagamento => 'Aguardando Pagamento',
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
        return $this === self::Rascunho || $this === self::AguardandoPagamento;
    }

    public function podeSerCancelado(): bool
    {
        return $this === self::Ativo || $this === self::AguardandoPagamento;
    }

    public function podeSerFinalizado(): bool
    {
        return $this === self::Ativo;
    }

    public function aguardandoPagamento(): bool
    {
        return $this === self::AguardandoPagamento;
    }
}
