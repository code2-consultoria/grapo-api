<?php

namespace App\Exceptions;

use Exception;

class ContratoAtivoImutavelException extends Exception
{
    public function __construct(public readonly string $codigoContrato)
    {
        $message = "O contrato '{$codigoContrato}' está ativo e não pode ser alterado. " .
            "Utilize um aditivo para realizar alterações.";

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
        ], 422);
    }
}
