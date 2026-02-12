<?php

namespace App\Contracts\Documentos;

interface FormataDocumento
{
    /**
     * Formata o número do documento para exibição
     */
    public function formatar(string $numero): string;

    /**
     * Remove a formatação do documento (apenas números)
     */
    public function limpar(string $numero): string;

    /**
     * Retorna a máscara do documento
     */
    public function mascara(): string;
}
