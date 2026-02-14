<?php

namespace App\Actions\Contrato\Aditivo;

use App\Contracts\Command;
use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use App\Exceptions\AditivoNaoPodeSerCriadoException;
use App\Models\Contrato;
use App\Models\ContratoAditivo;
use DateTime;

/**
 * Cria um novo aditivo para um contrato ativo.
 */
class Criar implements Command
{
    private ContratoAditivo $aditivo;

    public function __construct(
        private Contrato $contrato,
        private TipoAditivo $tipo,
        private DateTime $dataVigencia,
        private ?string $descricao = null,
        private ?DateTime $novaDataTermino = null,
        private ?float $valorAjuste = null,
        private bool $concederReembolso = false,
    ) {}

    /**
     * Executa a criação do aditivo.
     *
     * @throws AditivoNaoPodeSerCriadoException
     */
    public function handle(): void
    {
        // RN01 - Contrato deve estar ativo
        if (! $this->contrato->estaAtivo()) {
            throw new AditivoNaoPodeSerCriadoException(
                $this->contrato->codigo,
                $this->contrato->status
            );
        }

        $this->aditivo = new ContratoAditivo([
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'data_vigencia' => $this->dataVigencia,
            'nova_data_termino' => $this->novaDataTermino,
            'valor_ajuste' => $this->valorAjuste,
            'conceder_reembolso' => $this->concederReembolso,
            'status' => StatusAditivo::Rascunho,
        ]);

        $this->aditivo->contrato()->associate($this->contrato);
        $this->aditivo->save();
    }

    /**
     * Retorna o aditivo criado.
     */
    public function getAditivo(): ContratoAditivo
    {
        return $this->aditivo;
    }
}
