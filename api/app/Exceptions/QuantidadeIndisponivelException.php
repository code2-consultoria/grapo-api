<?php

namespace App\Exceptions;

use Exception;

class QuantidadeIndisponivelException extends Exception
{
    public function __construct(
        public readonly string $tipoAtivo,
        public readonly string $tipoAtivoId,
        public readonly int $quantidadeSolicitada,
        public readonly int $quantidadeDisponivel
    ) {
        $message = 'Não há unidades disponíveis. Crie um lote com novas unidades.';

        parent::__construct($message, 422);
    }

    /**
     * Renderiza a exception para resposta HTTP.
     */
    public function render($request)
    {
        $faltam = $this->quantidadeSolicitada - $this->quantidadeDisponivel;

        return response()->json([
            'message' => $this->getMessage(),
            'error_type' => 'quantidade_indisponivel',
            'tipo_ativo' => $this->tipoAtivo,
            'tipo_ativo_id' => $this->tipoAtivoId,
            'quantidade_solicitada' => $this->quantidadeSolicitada,
            'quantidade_disponivel' => $this->quantidadeDisponivel,
            'quantidade_faltante' => $faltam,
        ], 422);
    }
}
