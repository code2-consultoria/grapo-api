<?php

namespace App\Queries\TipoAtivo;

use App\Contracts\Query;
use App\Models\Pessoa;
use App\Models\TipoAtivo;

class Obter implements Query
{
    public function __construct(
        private string $id,
        private ?Pessoa $locador = null
    ) {}

    public function handle(): TipoAtivo
    {
        $query = TipoAtivo::query();

        if ($this->locador) {
            $query->where('locador_id', $this->locador->id);
        }

        $tipoAtivo = $query->findOrFail($this->id);

        // Adiciona quantidade disponível
        $tipoAtivo->quantidade_disponivel = $tipoAtivo->quantidadeDisponivel();

        return $tipoAtivo;
    }
}
