<?php

namespace App\Enums;

enum TipoDocumento: string
{
    case Cpf = 'cpf';
    case Cnpj = 'cnpj';
    case Rg = 'rg';
    case Cnh = 'cnh';
    case Passaporte = 'passaporte';
    case InscricaoMunicipal = 'inscricao_municipal';
    case InscricaoEstadual = 'inscricao_estadual';
    case CadUnico = 'cad_unico';

    public function label(): string
    {
        return match ($this) {
            self::Cpf => 'CPF',
            self::Cnpj => 'CNPJ',
            self::Rg => 'RG',
            self::Cnh => 'CNH',
            self::Passaporte => 'Passaporte',
            self::InscricaoMunicipal => 'Inscrição Municipal',
            self::InscricaoEstadual => 'Inscrição Estadual',
            self::CadUnico => 'CadÚnico',
        };
    }

    /**
     * Retorna se o tipo de documento indica pessoa jurídica
     */
    public function isPessoaJuridica(): bool
    {
        return in_array($this, [
            self::Cnpj,
            self::InscricaoMunicipal,
            self::InscricaoEstadual,
        ]);
    }

    /**
     * Retorna se o tipo de documento indica pessoa física
     */
    public function isPessoaFisica(): bool
    {
        return in_array($this, [
            self::Cpf,
            self::Rg,
            self::Cnh,
            self::Passaporte,
            self::CadUnico,
        ]);
    }
}
