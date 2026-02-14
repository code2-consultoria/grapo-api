<?php

namespace App\Exceptions;

use App\Enums\StatusAditivo;
use Exception;

class AditivoImutavelException extends Exception
{
    public function __construct(
        public readonly string $aditivoId,
        public readonly StatusAditivo $statusAtual
    ) {
        $message = "O aditivo não pode ser editado pois está em status '{$statusAtual->label()}'.";

        parent::__construct($message, 422);
    }

    /**
     * Renderiza a exception para resposta HTTP.
     */
    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'aditivo_id' => $this->aditivoId,
            'status_atual' => $this->statusAtual->value,
        ], 422);
    }
}
