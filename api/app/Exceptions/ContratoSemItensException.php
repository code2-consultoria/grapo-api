<?php

namespace App\Exceptions;

use Exception;

class ContratoSemItensException extends Exception
{
    public function __construct(
        public readonly string $codigoContrato
    ) {
        $message = "O contrato '{$codigoContrato}' não pode ser ativado sem itens. ".
            'Adicione pelo menos um item ao contrato antes de ativá-lo.';

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
            'error_type' => 'contrato_sem_itens',
        ], 422);
    }
}
