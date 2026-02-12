<?php

namespace App\Actions\Contrato;

use App\Contracts\Command;
use App\Models\Contrato;
use App\Models\Pessoa;

/**
 * Cria um novo contrato em status rascunho.
 */
class Criar implements Command
{
    private Contrato $contrato;

    public function __construct(
        private Pessoa $locador,
        private Pessoa $locatario,
        private \DateTime $dataInicio,
        private \DateTime $dataTermino,
        private ?string $observacoes = null
    ) {}

    /**
     * Executa a criação do contrato.
     */
    public function handle(): void
    {
        $this->contrato = new Contrato([
            'codigo' => $this->gerarCodigo(),
            'data_inicio' => $this->dataInicio,
            'data_termino' => $this->dataTermino,
            'valor_total' => 0,
            'status' => 'rascunho',
            'observacoes' => $this->observacoes,
        ]);

        $this->contrato->locador()->associate($this->locador);
        $this->contrato->locatario()->associate($this->locatario);
        $this->contrato->save();
    }

    /**
     * Retorna o contrato criado.
     */
    public function getContrato(): Contrato
    {
        return $this->contrato;
    }

    /**
     * Gera código único para o contrato.
     */
    private function gerarCodigo(): string
    {
        $ultimoContrato = Contrato::where('locador_id', $this->locador->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($ultimoContrato && preg_match('/CTR-(\d+)/', $ultimoContrato->codigo, $matches)) {
            $numero = (int) $matches[1] + 1;
        } else {
            $numero = 1;
        }

        return sprintf('CTR-%04d', $numero);
    }
}
