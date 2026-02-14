<?php

namespace App\Queries\Lote;

use App\Contracts\Query;
use App\Models\Lote;
use App\Models\Pessoa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Listar implements Query
{
    public function __construct(
        private ?Pessoa $locador = null,
        private ?string $tipoAtivoId = null,
        private ?string $status = null,
        private ?string $search = null,
        private int $perPage = 15
    ) {}

    public function handle(): LengthAwarePaginator
    {
        $query = Lote::with('tipoAtivo');

        if ($this->locador) {
            $query->where('locador_id', $this->locador->id);
        }

        if ($this->tipoAtivoId) {
            $query->where('tipo_ativo_id', $this->tipoAtivoId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where('codigo', 'ilike', "%{$this->search}%");
        }

        return $query->orderBy('data_aquisicao', 'desc')->paginate($this->perPage);
    }
}
