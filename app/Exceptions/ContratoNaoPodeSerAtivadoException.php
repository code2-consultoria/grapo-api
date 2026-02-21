<?php

namespace App\Exceptions;

use Exception;

class ContratoNaoPodeSerAtivadoException extends Exception
{
    public function __construct(
        public readonly string $codigoContrato,
        public readonly string $statusAtual
    ) {
        $message = "O contrato '{$codigoContrato}' não pode ser ativado. ".
            "Status atual: {$statusAtual}. Apenas contratos em rascunho podem ser ativados.";

        parent::__construct($message, 422);
    }

    /**
     * Renderiza a exception para resposta HTTP.
     */
    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'codigo_contrato' => $this->codigoContrato,
            'status_atual' => $this->statusAtual,
        ], 422);
    }
}
