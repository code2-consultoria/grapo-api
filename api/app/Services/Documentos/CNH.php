<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class CNH implements ValidaDocumento, FormataDocumento
{
    public function validar(string $numero): bool
    {
        $cnh = $this->limpar($numero);

        if (strlen($cnh) !== 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $cnh)) {
            return false;
        }

        // Primeiro dígito verificador
        $soma = 0;
        $multiplicador = 9;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cnh[$i] * $multiplicador;
            $multiplicador--;
        }
        $resto = $soma % 11;
        $digito1 = ($resto >= 10) ? 0 : $resto;
        $incremento = ($resto === 10) ? 2 : 0;

        // Segundo dígito verificador
        $soma = 0;
        $multiplicador = 1;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cnh[$i] * $multiplicador;
            $multiplicador++;
        }
        $resto = ($soma % 11) + $incremento;
        $digito2 = ($resto >= 10) ? 0 : $resto;

        return (int) $cnh[9] === $digito1 && (int) $cnh[10] === $digito2;
    }

    public function mensagemErro(): string
    {
        return 'CNH inválida.';
    }

    public function formatar(string $numero): string
    {
        $cnh = $this->limpar($numero);

        if (strlen($cnh) !== 11) {
            return $numero;
        }

        return sprintf(
            '%s %s %s',
            substr($cnh, 0, 3),
            substr($cnh, 3, 5),
            substr($cnh, 8, 3)
        );
    }

    public function limpar(string $numero): string
    {
        return preg_replace('/\D/', '', $numero);
    }

    public function mascara(): string
    {
        return '### ##### ###';
    }
}
