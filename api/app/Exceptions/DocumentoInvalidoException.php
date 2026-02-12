<?php

namespace App\Exceptions;

use Exception;

class DocumentoInvalidoException extends Exception
{
    public function __construct(string $mensagem)
    {
        parent::__construct($mensagem);
    }
}
