<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class InscricaoMunicipal implements ValidaDocumento, FormataDocumento
{
    public function validar(string $numero): bool
    {
        $inscricao = $this->limpar($numero);

        // Inscrição municipal tem formato variável por município
        // Validação mínima: deve ter entre 5 e 20 dígitos
        return strlen($inscricao) >= 5 && strlen($inscricao) <= 20;
    }

    public function mensagemErro(): string
    {
        return 'Inscrição Municipal inválida. O número deve ter entre 5 e 20 dígitos.';
    }

    public function formatar(string $numero): string
    {
        // Formato variável por município, retorna sem formatação padrão
        return $numero;
    }

    public function limpar(string $numero): string
    {
        return preg_replace('/\D/', '', $numero);
    }

    public function mascara(): string
    {
        return '';
    }
}
