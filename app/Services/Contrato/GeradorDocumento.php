<?php

namespace App\Services\Contrato;

use App\Enums\TipoDocumento;
use App\Models\Contrato;
use App\Models\Pessoa;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class GeradorDocumento
{
    private Contrato $contrato;

    private TemplateProcessor $templateProcessor;

    public function __construct(Contrato $contrato)
    {
        $this->contrato = $contrato;
    }

    /**
     * Gera o documento DOCX preenchido com os dados do contrato.
     */
    public function gerar(): string
    {
        $templatePath = storage_path('app/templates/contrato.docx');

        if (! file_exists($templatePath)) {
            throw new \RuntimeException('Template de contrato não encontrado.');
        }

        $this->templateProcessor = new TemplateProcessor($templatePath);

        $this->preencherDadosContrato();
        $this->preencherDadosLocador();
        $this->preencherDadosLocatario();
        $this->preencherItens();

        $outputPath = $this->gerarCaminhoSaida();
        $this->templateProcessor->saveAs($outputPath);

        return $outputPath;
    }

    /**
     * Preenche os dados básicos do contrato.
     */
    private function preencherDadosContrato(): void
    {
        $this->templateProcessor->setValue('CONTRATO_CODIGO', $this->contrato->codigo);
        $this->templateProcessor->setValue('DATA_INICIO', $this->formatarData($this->contrato->data_inicio));
        $this->templateProcessor->setValue('DATA_TERMINO', $this->formatarData($this->contrato->data_termino));
        $this->templateProcessor->setValue('VALOR_TOTAL', $this->formatarValor($this->contrato->valor_total));
        $this->templateProcessor->setValue('DIA_VENCIMENTO', $this->contrato->dia_vencimento ?? '-');
        $this->templateProcessor->setValue('OBSERVACOES', $this->contrato->observacoes ?? 'Sem observações.');
        $this->templateProcessor->setValue('DATA_GERACAO', $this->formatarData(Carbon::now()));
    }

    /**
     * Preenche os dados do locador.
     */
    private function preencherDadosLocador(): void
    {
        $locador = $this->contrato->locador;

        $this->templateProcessor->setValue('LOCADOR_NOME', $locador?->nome ?? '-');
        $this->templateProcessor->setValue('LOCADOR_DOCUMENTO', $this->obterDocumentoPrincipal($locador));
        $this->templateProcessor->setValue('LOCADOR_ENDERECO', $locador?->endereco ?? '-');
        $this->templateProcessor->setValue('LOCADOR_TELEFONE', $locador?->telefone ?? '-');
        $this->templateProcessor->setValue('LOCADOR_EMAIL', $locador?->email ?? '-');
    }

    /**
     * Preenche os dados do locatário.
     */
    private function preencherDadosLocatario(): void
    {
        $locatario = $this->contrato->locatario;

        $this->templateProcessor->setValue('LOCATARIO_NOME', $locatario?->nome ?? '-');
        $this->templateProcessor->setValue('LOCATARIO_DOCUMENTO', $this->obterDocumentoPrincipal($locatario));
        $this->templateProcessor->setValue('LOCATARIO_ENDERECO', $locatario?->endereco ?? '-');
        $this->templateProcessor->setValue('LOCATARIO_TELEFONE', $locatario?->telefone ?? '-');
        $this->templateProcessor->setValue('LOCATARIO_EMAIL', $locatario?->email ?? '-');
    }

    /**
     * Preenche a tabela de itens do contrato.
     */
    private function preencherItens(): void
    {
        $itens = $this->contrato->itens->load('tipoAtivo');

        if ($itens->isEmpty()) {
            $this->templateProcessor->setValue('ITENS_TABELA', 'Nenhum item cadastrado.');

            return;
        }

        $textoItens = '';
        foreach ($itens as $index => $item) {
            $numero = $index + 1;
            $nome = $item->tipoAtivo?->nome ?? 'Item sem descrição';
            $quantidade = $item->quantidade;
            $valorUnitario = $this->formatarValor($item->valor_unitario);
            $valorTotal = $this->formatarValor($item->valor_total_item);
            $periodo = $item->periodo_aluguel ?? 'mensal';

            $textoItens .= "{$numero}. {$nome} - Qtd: {$quantidade} x R$ {$valorUnitario} ({$periodo}) = R$ {$valorTotal}\n";
        }

        $this->templateProcessor->setValue('ITENS_TABELA', trim($textoItens));
    }

    /**
     * Obtém o documento principal (CPF ou CNPJ) de uma pessoa.
     */
    private function obterDocumentoPrincipal(?Pessoa $pessoa): string
    {
        if (! $pessoa) {
            return '-';
        }

        // Prioriza CNPJ para PJ, CPF para PF
        $documentoCnpj = $pessoa->documentos()
            ->where('tipo', TipoDocumento::Cnpj)
            ->first();

        if ($documentoCnpj) {
            return "CNPJ: {$documentoCnpj->numero_formatado}";
        }

        $documentoCpf = $pessoa->documentos()
            ->where('tipo', TipoDocumento::Cpf)
            ->first();

        if ($documentoCpf) {
            return "CPF: {$documentoCpf->numero_formatado}";
        }

        return '-';
    }

    /**
     * Formata uma data para exibição.
     */
    private function formatarData($data): string
    {
        if (! $data) {
            return '-';
        }

        if ($data instanceof Carbon) {
            return $data->format('d/m/Y');
        }

        return Carbon::parse($data)->format('d/m/Y');
    }

    /**
     * Formata um valor monetário.
     */
    private function formatarValor($valor): string
    {
        if (! $valor) {
            return '0,00';
        }

        return number_format((float) $valor, 2, ',', '.');
    }

    /**
     * Gera o caminho do arquivo de saída.
     */
    private function gerarCaminhoSaida(): string
    {
        $diretorio = storage_path('app/contratos/gerados');

        if (! is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $nomeArquivo = "contrato-{$this->contrato->codigo}.docx";

        return "{$diretorio}/{$nomeArquivo}";
    }
}
