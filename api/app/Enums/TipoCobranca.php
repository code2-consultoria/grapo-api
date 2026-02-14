<?php

namespace App\Enums;

enum TipoCobranca: string
{
    case AntecipadoStripe = 'antecipado_stripe';
    case AntecipadoPix = 'antecipado_pix';
    case RecorrenteStripe = 'recorrente_stripe';
    case RecorrenteManual = 'recorrente_manual';
    case SemCobranca = 'sem_cobranca';

    public function label(): string
    {
        return match ($this) {
            self::AntecipadoStripe => 'Antecipado (Cartão)',
            self::AntecipadoPix => 'Antecipado (PIX)',
            self::RecorrenteStripe => 'Recorrente (Cartão)',
            self::RecorrenteManual => 'Recorrente (Manual)',
            self::SemCobranca => 'Sem Cobrança',
        };
    }

    public function isAntecipado(): bool
    {
        return $this === self::AntecipadoStripe || $this === self::AntecipadoPix;
    }

    public function isRecorrente(): bool
    {
        return $this === self::RecorrenteStripe || $this === self::RecorrenteManual;
    }

    public function isStripe(): bool
    {
        return $this === self::AntecipadoStripe || $this === self::RecorrenteStripe;
    }

    public function isManual(): bool
    {
        return $this === self::RecorrenteManual;
    }

    public function isPix(): bool
    {
        return $this === self::AntecipadoPix;
    }

    public function exigePagamentoParaAtivar(): bool
    {
        return $this->isAntecipado();
    }
}
