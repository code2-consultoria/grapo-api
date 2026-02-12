<?php

namespace App\Contracts\Documentos;

interface ValidaDocumento
{
    /**
     * Valida o número do documento
     */
    public function validar(string $numero): bool;

    /**
     * Retorna a mensagem de erro de validação
     */
    public function mensagemErro(): string;
}
