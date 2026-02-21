<?php

namespace App\Exceptions;

use Exception;

class ReducaoExcedeQuantidadeException extends Exception
{
    public function __construct(
        public readonly string $tipoAtivoNome,
        public readonly int $quantidadeReduzir,
        public readonly int $quantidadeAlocada
    ) {
        $message = "Não é possível reduzir {$quantidadeReduzir} unidades de '{$tipoAtivoNome}'. ".
            "Quantidade atual alocada: {$quantidadeAlocada}.";

        parent::__construct($message, 422);
    }

    /**
     * Renderiza a exception para resposta HTTP.
     */
    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'tipo_ativo' => $this->tipoAtivoNome,
            'quantidade_reduzir' => $this->quantidadeReduzir,
            'quantidade_alocada' => $this->quantidadeAlocada,
        ], 422);
    }
}
