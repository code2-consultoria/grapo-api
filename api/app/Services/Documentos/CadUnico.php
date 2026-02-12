<?php

namespace App\Services\Documentos;

use App\Contracts\Documentos\FormataDocumento;
use App\Contracts\Documentos\ValidaDocumento;

class CadUnico implements ValidaDocumento, FormataDocumento
{
    public function validar(string $numero): bool
    {
        $nis = $this->limpar($numero);

        if (strlen($nis) !== 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $nis)) {
            return false;
        }

        // Validação do dígito verificador (módulo 11)
        $pesos = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;

        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $nis[$i] * $pesos[$i];
        }

        $resto = $soma % 11;
        $digito = ($resto < 2) ? 0 : 11 - $resto;

        return (int) $nis[10] === $digito;
    }

    public function mensagemErro(): string
    {
        return 'NIS/CadÚnico inválido.';
    }

    public function formatar(string $numero): string
    {
        $nis = $this->limpar($numero);

        if (strlen($nis) !== 11) {
            return $numero;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($nis, 0, 3),
            substr($nis, 3, 5),
            substr($nis, 8, 2),
            substr($nis, 10, 1)
        );
    }

    public function limpar(string $numero): string
    {
        return preg_replace('/\D/', '', $numero);
    }

    public function mascara(): string
    {
        return '###.#####.##-#';
    }
}
