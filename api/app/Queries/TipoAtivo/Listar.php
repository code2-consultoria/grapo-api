<?php

namespace App\Queries\TipoAtivo;

use App\Contracts\Query;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Listar implements Query
{
    public function __construct(
        private ?Pessoa $locador = null,
        private ?string $search = null,
        private int $perPage = 15
    ) {}

    public function handle(): LengthAwarePaginator
    {
        $query = TipoAtivo::query();

        if ($this->locador) {
            $query->where('locador_id', $this->locador->id);
        }

        if ($this->search) {
            $query->where('nome', 'ilike', "%{$this->search}%");
        }

        return $query->orderBy('nome')->paginate($this->perPage);
    }
}
