<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;
use App\Enums\TipoDocumento;
use InvalidArgumentException;

class DocumentoFactory
{
    /**
     * Cria uma instância do validador/formatador de documento
     *
     * @throws InvalidArgumentException
     */
    public static function criar(TipoDocumento $tipo): ValidaDocumento&FormataDocumento
    {
        return match ($tipo) {
            TipoDocumento::Cpf => new CPF(),
            TipoDocumento::Cnpj => new CNPJ(),
            TipoDocumento::Rg => new RG(),
            TipoDocumento::Cnh => new CNH(),
            TipoDocumento::Passaporte => new Passaporte(),
            TipoDocumento::InscricaoMunicipal => new InscricaoMunicipal(),
            TipoDocumento::InscricaoEstadual => new InscricaoEstadual(),
            TipoDocumento::CadUnico => new CadUnico(),
        };
    }

    /**
     * Valida um documento de acordo com seu tipo
     */
    public static function validar(TipoDocumento $tipo, string $numero): bool
    {
        $documento = self::criar($tipo);

        return $documento->validar($numero);
    }

    /**
     * Formata um documento de acordo com seu tipo
     */
    public static function formatar(TipoDocumento $tipo, string $numero): string
    {
        $documento = self::criar($tipo);

        return $documento->formatar($numero);
    }

    /**
     * Limpa a formatação de um documento
     */
    public static function limpar(TipoDocumento $tipo, string $numero): string
    {
        $documento = self::criar($tipo);

        return $documento->limpar($numero);
    }

    /**
     * Retorna a mensagem de erro para o tipo de documento
     */
    public static function mensagemErro(TipoDocumento $tipo): string
    {
        $documento = self::criar($tipo);

        return $documento->mensagemErro();
    }
}
