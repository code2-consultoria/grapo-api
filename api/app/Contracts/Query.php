<?php

namespace App\Contracts;

interface Query
{
    /**
     * Executa a query e retorna o resultado
     */
    public function handle(): mixed;
}
