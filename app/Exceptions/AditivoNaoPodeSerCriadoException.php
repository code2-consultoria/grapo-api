<?php

namespace App\Exceptions;

use App\Enums\StatusContrato;
use Exception;

class AditivoNaoPodeSerCriadoException extends Exception
{
    public function __construct(
        public readonly string $codigoContrato,
        public readonly StatusContrato $statusAtual
    ) {
        $message = 'Aditivos só podem ser criados para contratos ativos. '.
            "O contrato '{$codigoContrato}' está em status '{$statusAtual->label()}'.";

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
            'status_atual' => $this->statusAtual->value,
        ], 422);
    }
}
