<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ContratoSemParcelasException extends Exception
{
    public function __construct(
        public readonly string $codigoContrato
    ) {
        parent::__construct(
            "O contrato {$codigoContrato} nao possui parcelas cadastradas. Adicione as parcelas antes de ativar."
        );
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'codigo_contrato' => $this->codigoContrato,
            'error_type' => 'contrato_sem_parcelas',
        ], 422);
    }
}
