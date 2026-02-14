<?php

namespace App\Actions\TipoAtivo;

use App\Contracts\Command;
use App\Models\Pessoa;
use App\Models\TipoAtivo;

/**
 * Cria um novo tipo de ativo.
 */
class Criar implements Command
{
    private TipoAtivo $tipoAtivo;

    public function __construct(
        private Pessoa $locador,
        private string $nome,
        private ?string $descricao = null,
        private string $unidadeMedida = 'unidade',
        private ?float $valorMensalSugerido = null
    ) {}

    /**
     * Executa a criação do tipo de ativo.
     */
    public function handle(): void
    {
        $this->tipoAtivo = new TipoAtivo([
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'unidade_medida' => $this->unidadeMedida,
            'valor_mensal_sugerido' => $this->valorMensalSugerido,
        ]);

        $this->tipoAtivo->locador()->associate($this->locador);
        $this->tipoAtivo->save();
    }

    /**
     * Retorna o tipo de ativo criado.
     */
    public function getTipoAtivo(): TipoAtivo
    {
        return $this->tipoAtivo;
    }
}
