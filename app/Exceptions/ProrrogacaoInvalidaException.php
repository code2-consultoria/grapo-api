<?php

namespace App\Exceptions;

use Exception;

class ProrrogacaoInvalidaException extends Exception
{
    public function __construct(
        public readonly string $dataTerminoAtual,
        public readonly string $novaDataTermino
    ) {
        $message = "A nova data de término ({$novaDataTermino}) deve ser posterior à data atual ({$dataTerminoAtual}).";

        parent::__construct($message, 422);
    }

    /**
     * Renderiza a exception para resposta HTTP.
     */
    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'data_termino_atual' => $this->dataTerminoAtual,
            'nova_data_termino' => $this->novaDataTermino,
        ], 422);
    }
}
