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
     * Detecta o tipo de documento (CPF ou CNPJ) pelo tamanho
     *
     * @throws InvalidArgumentException
     */
    public static function detectarTipo(string $numero): TipoDocumento
    {
        // Remove caracteres não numéricos
        $numeroLimpo = preg_replace('/\D/', '', $numero);

        return match (strlen($numeroLimpo)) {
            11 => TipoDocumento::Cpf,
            14 => TipoDocumento::Cnpj,
            default => throw new InvalidArgumentException(
                'Documento deve ter 11 (CPF) ou 14 (CNPJ) dígitos.'
            ),
        };
    }

    /**
     * Valida um documento detectando automaticamente o tipo
     */
    public static function validarAuto(string $numero): bool
    {
        try {
            $tipo = self::detectarTipo($numero);

            return self::validar($tipo, $numero);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Limpa a formatação e detecta o tipo automaticamente
     *
     * @return array{tipo: TipoDocumento, numero: string}
     *
     * @throws InvalidArgumentException
     */
    public static function processarAuto(string $numero): array
    {
        $tipo = self::detectarTipo($numero);
        $numeroLimpo = self::limpar($tipo, $numero);

        return [
            'tipo' => $tipo,
            'numero' => $numeroLimpo,
        ];
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
