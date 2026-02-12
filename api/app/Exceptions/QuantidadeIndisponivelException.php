<?php

namespace App\Exceptions;

use Exception;

class QuantidadeIndisponivelException extends Exception
{
    public function __construct(
        public readonly string $tipoAtivo,
        public readonly int $quantidadeSolicitada,
        public readonly int $quantidadeDisponivel
    ) {
        $message = "Quantidade indisponível para '{$tipoAtivo}'. " .
            "Solicitado: {$quantidadeSolicitada}, Disponível: {$quantidadeDisponivel}";

        parent::__construct($message, 422);
    }

    /**
     * Renderiza a exception para resposta HTTP.
     */
    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'tipo_ativo' => $this->tipoAtivo,
            'quantidade_solicitada' => $this->quantidadeSolicitada,
            'quantidade_disponivel' => $this->quantidadeDisponivel,
        ], 422);
    }
}
