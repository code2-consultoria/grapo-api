<?php

namespace App\Enums;

enum TipoPessoa: string
{
    case Locador = 'locador';
    case Locatario = 'locatario';
    case ResponsavelFinanceiro = 'responsavel_fin';
    case ResponsavelAdministrativo = 'responsavel_adm';

    public function label(): string
    {
        return match ($this) {
            self::Locador => 'Locador',
            self::Locatario => 'Locatário',
            self::ResponsavelFinanceiro => 'Responsável Financeiro',
            self::ResponsavelAdministrativo => 'Responsável Administrativo',
        };
    }
}
