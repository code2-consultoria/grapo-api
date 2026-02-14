<?php

namespace App\Queries\Contrato;

use App\Contracts\Query;
use App\Models\Contrato;
use App\Models\Pessoa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Listar implements Query
{
    public function __construct(
        private ?Pessoa $locador = null,
        private ?string $status = null,
        private ?string $locatarioId = null,
        private ?string $search = null,
        private int $perPage = 15
    ) {}

    public function handle(): LengthAwarePaginator
    {
        $query = Contrato::with(['locador', 'locatario', 'itens.tipoAtivo']);

        if ($this->locador) {
            $query->where('locador_id', $this->locador->id);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->locatarioId) {
            $query->where('locatario_id', $this->locatarioId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'ilike', "%{$this->search}%")
                    ->orWhereHas('locatario', function ($q2) {
                        $q2->where('nome', 'ilike', "%{$this->search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }
}
