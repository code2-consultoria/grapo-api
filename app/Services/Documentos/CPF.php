<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class CPF implements FormataDocumento, ValidaDocumento
{
    public function validar(string $numero): bool
    {
        $cpf = $this->limpar($numero);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        if ((int) $cpf[9] !== $digito1) {
            return false;
        }

        // Validação do segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $cpf[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        return (int) $cpf[10] === $digito2;
    }

    public function mensagemErro(): string
    {
        return 'CPF inválido.';
    }

    public function formatar(string $numero): string
    {
        $cpf = $this->limpar($numero);

        if (strlen($cpf) !== 11) {
            return $numero;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9, 2)
        );
    }

    public function limpar(string $numero): string
    {
        return preg_replace('/\D/', '', $numero);
    }

    public function mascara(): string
    {
        return '###.###.###-##';
    }
}
