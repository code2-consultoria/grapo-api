<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class GerarTemplateContrato extends Command
{
    protected $signature = 'contrato:gerar-template';

    protected $description = 'Gera o template DOCX para contratos de locação';

    public function handle(): int
    {
        $phpWord = new PhpWord;

        // Estilos
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16], ['alignment' => 'center', 'spaceAfter' => 240]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 12], ['spaceAfter' => 120, 'spaceBefore' => 240]);

        $section = $phpWord->addSection();

        // Título
        $section->addTitle('CONTRATO DE LOCAÇÃO DE BENS MÓVEIS', 1);

        // Número do contrato
        $section->addText('Contrato nº: ${CONTRATO_CODIGO}', ['bold' => true], ['spaceAfter' => 240]);

        // Locador
        $section->addTitle('LOCADOR', 2);
        $section->addText('Nome: ${LOCADOR_NOME}');
        $section->addText('CPF/CNPJ: ${LOCADOR_DOCUMENTO}');
        $section->addText('Endereço: ${LOCADOR_ENDERECO}');
        $section->addText('Telefone: ${LOCADOR_TELEFONE}');
        $section->addText('E-mail: ${LOCADOR_EMAIL}');

        // Locatário
        $section->addTitle('LOCATÁRIO', 2);
        $section->addText('Nome: ${LOCATARIO_NOME}');
        $section->addText('CPF/CNPJ: ${LOCATARIO_DOCUMENTO}');
        $section->addText('Endereço: ${LOCATARIO_ENDERECO}');
        $section->addText('Telefone: ${LOCATARIO_TELEFONE}');
        $section->addText('E-mail: ${LOCATARIO_EMAIL}');

        // Objeto
        $section->addTitle('OBJETO', 2);
        $section->addText('Locação dos seguintes bens:');
        $section->addTextBreak();
        $section->addText('${ITENS_TABELA}');

        // Condições
        $section->addTitle('CONDIÇÕES', 2);
        $section->addText('PERÍODO: De ${DATA_INICIO} a ${DATA_TERMINO}');
        $section->addText('VALOR TOTAL: R$ ${VALOR_TOTAL}');
        $section->addText('VENCIMENTO: Dia ${DIA_VENCIMENTO} de cada mês');

        // Observações
        $section->addTitle('OBSERVAÇÕES', 2);
        $section->addText('${OBSERVACOES}');

        // Cláusulas
        $section->addTitle('CLÁUSULAS', 2);
        $section->addText('1. O LOCATÁRIO se compromete a utilizar os bens locados com zelo e cuidado, respondendo por quaisquer danos causados.');
        $section->addTextBreak();
        $section->addText('2. O LOCATÁRIO deverá devolver os bens nas mesmas condições em que os recebeu, ressalvado o desgaste natural pelo uso.');
        $section->addTextBreak();
        $section->addText('3. O LOCADOR poderá rescindir o contrato em caso de inadimplência superior a 30 dias.');
        $section->addTextBreak();
        $section->addText('4. As partes elegem o foro da comarca do LOCADOR para dirimir quaisquer questões oriundas deste contrato.');

        // Assinaturas
        $section->addTextBreak(2);
        $section->addText('Local e data: _________________, ${DATA_GERACAO}', [], ['spaceAfter' => 480]);

        $section->addTextBreak(2);
        $section->addText('_______________________________', [], ['alignment' => 'center']);
        $section->addText('${LOCADOR_NOME}', ['bold' => true], ['alignment' => 'center']);
        $section->addText('LOCADOR', [], ['alignment' => 'center']);

        $section->addTextBreak(2);
        $section->addText('_______________________________', [], ['alignment' => 'center']);
        $section->addText('${LOCATARIO_NOME}', ['bold' => true], ['alignment' => 'center']);
        $section->addText('LOCATÁRIO', [], ['alignment' => 'center']);

        // Salvar
        $path = storage_path('app/templates/contrato.docx');
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($path);

        $this->info("Template gerado com sucesso em: {$path}");

        return Command::SUCCESS;
    }
}
