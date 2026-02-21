<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class Passaporte implements FormataDocumento, ValidaDocumento
{
    public function validar(string $numero): bool
    {
        $passaporte = trim($numero);

        // Passaporte brasileiro: 2 letras + 6 dígitos
        // Aceita também formatos internacionais variados
        return preg_match('/^[A-Z]{2}[0-9]{6}$/', strtoupper($passaporte))
            || preg_match('/^[A-Z0-9]{6,12}$/', strtoupper($passaporte));
    }

    public function mensagemErro(): string
    {
        return 'Passaporte inválido. Use o formato brasileiro (XX000000) ou formato internacional.';
    }

    public function formatar(string $numero): string
    {
        return strtoupper(trim($numero));
    }

    public function limpar(string $numero): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $numero);
    }

    public function mascara(): string
    {
        return 'AA######';
    }
}
