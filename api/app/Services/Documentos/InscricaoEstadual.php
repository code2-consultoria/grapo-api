<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class InscricaoEstadual implements FormataDocumento, ValidaDocumento
{
    public function validar(string $numero): bool
    {
        $inscricao = $this->limpar($numero);

        // Inscrição estadual tem formato variável por estado
        // Validação mínima: deve ter entre 8 e 14 dígitos
        return strlen($inscricao) >= 8 && strlen($inscricao) <= 14;
    }

    public function mensagemErro(): string
    {
        return 'Inscrição Estadual inválida. O número deve ter entre 8 e 14 dígitos.';
    }

    public function formatar(string $numero): string
    {
        // Formato variável por estado, retorna sem formatação padrão
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
