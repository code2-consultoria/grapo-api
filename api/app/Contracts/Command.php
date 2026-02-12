<?php

namespace App\Contracts;

interface Command
{
    /**
     * Executa o comando
     */
    public function handle(): void;
}
