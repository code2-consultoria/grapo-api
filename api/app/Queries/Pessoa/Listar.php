<?php

namespace App\Queries\Pessoa;

use App\Contracts\Query;
use App\Enums\TipoPessoa;
use App\Models\Pessoa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Listar implements Query
{
    public function __construct(
        private ?Pessoa $locador = null,
        private ?TipoPessoa $tipo = null,
        private ?string $search = null,
        private ?bool $ativo = null,
        private int $perPage = 15
    ) {}

    public function handle(): LengthAwarePaginator
    {
        $query = Pessoa::query();

        if ($this->tipo) {
            $query->porTipo($this->tipo);
        }

        if ($this->search) {
            $query->where('nome', 'ilike', "%{$this->search}%");
        }

        if ($this->ativo !== null) {
            $query->where('ativo', $this->ativo);
        }

        // Filtragem por locador
        if ($this->locador) {
            // Se está buscando locadores, mostra apenas o próprio locador
            if ($this->tipo === TipoPessoa::Locador) {
                $query->where('id', $this->locador->id);
            } else {
                // Para outros tipos, filtra pelo locador_id
                $query->where('locador_id', $this->locador->id);
            }
        }

        return $query->with('documentos')->orderBy('nome')->paginate($this->perPage);
    }
}
