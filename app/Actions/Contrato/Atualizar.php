<?php

namespace App\Actions\Contrato;

use App\Actions\Contrato\Item\CalcularValorItem;
use App\Contracts\Command;
use App\Models\Contrato;
use App\Models\Pessoa;

/**
 * Atualiza um contrato em status rascunho.
 */
class Atualizar implements Command
{
    public function __construct(
        private Contrato $contrato,
        private Pessoa $locatario,
        private \DateTime $dataInicio,
        private \DateTime $dataTermino,
        private ?string $observacoes = null
    ) {}

    /**
     * Executa a atualização do contrato.
     */
    public function handle(): void
    {
        // Apenas contratos em rascunho podem ser atualizados
        if ($this->contrato->status !== 'rascunho') {
            throw new \DomainException('Apenas contratos em rascunho podem ser atualizados.');
        }

        $this->contrato->fill([
            'data_inicio' => $this->dataInicio,
            'data_termino' => $this->dataTermino,
            'observacoes' => $this->observacoes,
        ]);

        $this->contrato->locatario()->associate($this->locatario);
        $this->contrato->save();

        // Recalcula valores de cada item baseado no novo período
        $this->recalcularItens();

        // Recalcula o valor total
        $calcularValorTotal = new CalcularValorTotal($this->contrato);
        $calcularValorTotal->handle();
    }

    /**
     * Recalcula valores de todos os itens do contrato.
     */
    private function recalcularItens(): void
    {
        $diasLocacao = $this->calcularDiasLocacao();

        $this->contrato->load('itens');
        foreach ($this->contrato->itens as $item) {
            $calcularValorItem = new CalcularValorItem($item, $diasLocacao);
            $calcularValorItem->handle();
        }
    }

    /**
     * Calcula dias de locação.
     */
    private function calcularDiasLocacao(): int
    {
        $diff = $this->dataInicio->diff($this->dataTermino);
        return $diff->days + 1;
    }

    /**
     * Retorna o contrato atualizado.
     */
    public function getContrato(): Contrato
    {
        return $this->contrato;
    }
}
