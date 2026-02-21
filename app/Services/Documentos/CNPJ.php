<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class CNPJ implements FormataDocumento, ValidaDocumento
{
    public function validar(string $numero): bool
    {
        $cnpj = $this->limpar($numero);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $pesos = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $pesos[$i];
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        if ((int) $cnpj[12] !== $digito1) {
            return false;
        }

        // Validação do segundo dígito verificador
        $pesos = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $pesos[$i];
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        return (int) $cnpj[13] === $digito2;
    }

    public function mensagemErro(): string
    {
        return 'CNPJ inválido.';
    }

    public function formatar(string $numero): string
    {
        $cnpj = $this->limpar($numero);

        if (strlen($cnpj) !== 14) {
            return $numero;
        }

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }

    public function limpar(string $numero): string
    {
        return preg_replace('/\D/', '', $numero);
    }

    public function mascara(): string
    {
        return '##.###.###/####-##';
    }
}
