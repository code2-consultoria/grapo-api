<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class RG implements ValidaDocumento, FormataDocumento
{
    public function validar(string $numero): bool
    {
        $rg = $this->limpar($numero);

        // RG tem formato variável por estado, validação mínima
        return strlen($rg) >= 5 && strlen($rg) <= 14;
    }

    public function mensagemErro(): string
    {
        return 'RG inválido. O número deve ter entre 5 e 14 caracteres.';
    }

    public function formatar(string $numero): string
    {
        // RG tem formato variável por estado, retorna sem formatação padrão
        return $numero;
    }

    public function limpar(string $numero): string
    {
        return preg_replace('/[^0-9Xx]/', '', $numero);
    }

    public function mascara(): string
    {
        return '##.###.###-#';
    }
}
