<?php

namespace App\Actions\Contrato;

use App\Actions\Alocacao\Alocar;
use App\Contracts\Command;
use App\Enums\StatusContrato;
use App\Exceptions\ContratoNaoPodeSerAtivadoException;
use App\Exceptions\ContratoSemItensException;
use App\Exceptions\ContratoSemParcelasException;
use App\Exceptions\QuantidadeIndisponivelException;
use App\Models\Contrato;
use Illuminate\Support\Facades\DB;

/**
 * Ativa um contrato e aloca os lotes necessários.
 */
class Ativar implements Command
{
    public function __construct(
        private Contrato $contrato
    ) {}

    /**
     * Executa a ativação do contrato.
     *
     * @throws ContratoNaoPodeSerAtivadoException
     * @throws ContratoSemItensException
     * @throws ContratoSemParcelasException
     * @throws QuantidadeIndisponivelException
     */
    public function handle(): void
    {
        // Valida se pode ser ativado
        if (! $this->contrato->estaEmRascunho()) {
            throw new ContratoNaoPodeSerAtivadoException(
                $this->contrato->codigo,
                $this->contrato->status
            );
        }

        // Valida se tem itens
        if ($this->contrato->itens->isEmpty()) {
            throw new ContratoSemItensException($this->contrato->codigo);
        }

        // Valida se contrato recorrente manual tem parcelas
        if ($this->contrato->temCobrancaManual() && $this->contrato->pagamentos->isEmpty()) {
            throw new ContratoSemParcelasException($this->contrato->codigo);
        }

        // Executa alocação e ativação em transação
        DB::transaction(function () {
            // Aloca lotes para cada item
            foreach ($this->contrato->itens as $item) {
                (new Alocar($item))->handle();
            }

            // Define status baseado no tipo de cobranca
            // Se exige pagamento antecipado, vai para aguardando_pagamento
            // Caso contrario, vai direto para ativo
            $novoStatus = $this->contrato->exigePagamentoAntecipado()
                ? StatusContrato::AguardandoPagamento
                : StatusContrato::Ativo;

            $this->contrato->status = $novoStatus;
            $this->contrato->save();
        });
    }

    /**
     * Retorna o contrato atualizado.
     */
    public function getContrato(): Contrato
    {
        return $this->contrato->fresh();
    }
}
