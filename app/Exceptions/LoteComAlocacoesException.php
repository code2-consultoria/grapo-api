<?php

namespace App\Exceptions;

use Exception;

class LoteComAlocacoesException extends Exception
{
    public function __construct(string $codigoLote)
    {
        parent::__construct("Não é possível excluir o lote {$codigoLote} pois possui alocações ativas.");
    }
}
