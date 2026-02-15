<?php

use App\Enums\StatusPagamento;
use App\Models\AlocacaoLote;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Lote;
use App\Models\Pagamento;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;
use Carbon\Carbon;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create();
    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);

    // Criar tipos de ativos
    $this->tipoAtivo1 = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Cadeira',
    ]);

    $this->tipoAtivo2 = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Mesa',
    ]);

    // Criar locatarios
    $this->locatario1 = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Cliente A',
    ]);

    $this->locatario2 = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Cliente B',
    ]);
});

test('retorna estrutura correta do relatorio financeiro', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'periodo' => ['inicio', 'fim'],
        'resumo' => [
            'total_faturado',
            'total_recebido',
            'total_pendente',
            'total_atrasado',
            'taxa_inadimplencia',
        ],
        'faturamento_mensal',
        'faturamento_por_ativo',
        'inadimplencia' => [
            'quantidade',
            'valor_total',
            'pagamentos',
        ],
        'analitico_por_locatario',
    ]);
});

test('calcula total mensal de faturamento', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario1->id,
    ]);

    // Pagamentos em meses diferentes
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::create(2025, 10, 15),
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1500.00,
        'data_pagamento' => Carbon::create(2025, 11, 10),
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 2000.00,
        'data_pagamento' => Carbon::create(2025, 11, 20),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro?data_inicio=2025-10-01&data_fim=2025-11-30');

    $response->assertStatus(200);

    $faturamentoMensal = $response->json('faturamento_mensal');

    // Verifica que temos dados para outubro e novembro
    $outubro = collect($faturamentoMensal)->firstWhere('mes', '2025-10');
    $novembro = collect($faturamentoMensal)->firstWhere('mes', '2025-11');

    expect((float) $outubro['valor'])->toBe(1000.00);
    expect((float) $novembro['valor'])->toBe(3500.00); // 1500 + 2000
});

test('calcula total por tipo de ativo', function () {
    // Contrato com item do tipo Cadeira
    $contrato1 = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario1->id,
    ]);

    $item1 = ContratoItem::factory()->create([
        'contrato_id' => $contrato1->id,
        'tipo_ativo_id' => $this->tipoAtivo1->id,
        'quantidade' => 10,
    ]);

    $lote1 = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo1->id,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item1->id,
        'lote_id' => $lote1->id,
        'quantidade_alocada' => 10,
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato1->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::now(),
    ]);

    // Contrato com item do tipo Mesa
    $contrato2 = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario2->id,
    ]);

    $item2 = ContratoItem::factory()->create([
        'contrato_id' => $contrato2->id,
        'tipo_ativo_id' => $this->tipoAtivo2->id,
        'quantidade' => 5,
    ]);

    $lote2 = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo2->id,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item2->id,
        'lote_id' => $lote2->id,
        'quantidade_alocada' => 5,
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato2->id,
        'valor' => 500.00,
        'data_pagamento' => Carbon::now(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);

    $faturamentoPorAtivo = $response->json('faturamento_por_ativo');

    expect(count($faturamentoPorAtivo))->toBeGreaterThanOrEqual(2);

    $cadeira = collect($faturamentoPorAtivo)->firstWhere('tipo_ativo', 'Cadeira');
    $mesa = collect($faturamentoPorAtivo)->firstWhere('tipo_ativo', 'Mesa');

    expect((float) $cadeira['valor'])->toBe(1000.00);
    expect((float) $mesa['valor'])->toBe(500.00);
});

test('calcula metricas de inadimplencia', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario1->id,
    ]);

    // Pagamento atrasado
    Pagamento::factory()->atrasado()->create([
        'contrato_id' => $contrato->id,
        'valor' => 500.00,
        'data_vencimento' => Carbon::now()->subDays(10),
    ]);

    // Pagamento pendente vencido (deve ser considerado atrasado)
    Pagamento::factory()->pendente()->create([
        'contrato_id' => $contrato->id,
        'valor' => 300.00,
        'data_vencimento' => Carbon::now()->subDays(5),
    ]);

    // Pagamento pago (nao deve contar)
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::now(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);

    $inadimplencia = $response->json('inadimplencia');

    expect($inadimplencia['quantidade'])->toBe(2);
    expect((float) $inadimplencia['valor_total'])->toBe(800.00);
    expect(count($inadimplencia['pagamentos']))->toBe(2);
});

test('retorna analitico de pagamentos por locatario', function () {
    // Contrato do locatario 1
    $contrato1 = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario1->id,
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato1->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::now(),
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato1->id,
        'valor' => 500.00,
        'data_pagamento' => Carbon::now()->subDays(5),
    ]);

    // Contrato do locatario 2
    $contrato2 = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario2->id,
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato2->id,
        'valor' => 2000.00,
        'data_pagamento' => Carbon::now(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);

    $analitico = $response->json('analitico_por_locatario');

    expect(count($analitico))->toBe(2);

    $clienteA = collect($analitico)->firstWhere('locatario', 'Cliente A');
    $clienteB = collect($analitico)->firstWhere('locatario', 'Cliente B');

    expect((float) $clienteA['total_pago'])->toBe(1500.00);
    expect($clienteA['qtd_pagamentos'])->toBe(2);
    expect((float) $clienteB['total_pago'])->toBe(2000.00);
    expect($clienteB['qtd_pagamentos'])->toBe(1);
});

test('filtra por periodo de datas', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario1->id,
    ]);

    // Pagamento em outubro (dentro do filtro)
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::create(2025, 10, 15),
    ]);

    // Pagamento em setembro (fora do filtro)
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 2000.00,
        'data_pagamento' => Carbon::create(2025, 9, 15),
    ]);

    // Pagamento em novembro (fora do filtro)
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 3000.00,
        'data_pagamento' => Carbon::create(2025, 11, 15),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro?data_inicio=2025-10-01&data_fim=2025-10-31');

    $response->assertStatus(200);

    // Apenas o pagamento de outubro deve ser considerado
    $resumo = $response->json('resumo');
    expect((float) $resumo['total_recebido'])->toBe(1000.00);
});

test('calcula taxa de inadimplencia corretamente', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario1->id,
    ]);

    // Total faturado: 2000 (1000 pago + 500 atrasado + 500 pendente vencido)
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::now(),
    ]);

    Pagamento::factory()->atrasado()->create([
        'contrato_id' => $contrato->id,
        'valor' => 500.00,
        'data_vencimento' => Carbon::now()->subDays(10),
    ]);

    Pagamento::factory()->pendente()->create([
        'contrato_id' => $contrato->id,
        'valor' => 500.00,
        'data_vencimento' => Carbon::now()->subDays(5),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);

    $resumo = $response->json('resumo');

    // Taxa de inadimplencia = (atrasado / faturado) * 100 = (1000 / 2000) * 100 = 50%
    expect((float) $resumo['taxa_inadimplencia'])->toBe(50.00);
});

test('nao retorna dados de outro locador', function () {
    // Cria outro locador com dados
    $outroLocador = Pessoa::factory()->locador()->create();
    $outroLocatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $outroLocador->id,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $outroLocador->id,
        'locatario_id' => $outroLocatario->id,
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 5000.00,
        'data_pagamento' => Carbon::now(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);

    // Nao deve incluir pagamentos do outro locador
    $resumo = $response->json('resumo');
    expect((float) $resumo['total_recebido'])->toBe(0.00);
});

test('considera desconto comercial no valor dos pagamentos', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario1->id,
    ]);

    // Pagamento com desconto
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'desconto_comercial' => 200.00,
        'data_pagamento' => Carbon::now(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);

    // Deve considerar valor final (1000 - 200 = 800)
    $resumo = $response->json('resumo');
    expect((float) $resumo['total_recebido'])->toBe(800.00);
});

test('retorna periodo padrao quando nao informado', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/relatorios/financeiro');

    $response->assertStatus(200);

    $periodo = $response->json('periodo');

    // Deve retornar os ultimos 12 meses como padrao
    expect($periodo['inicio'])->not->toBeNull();
    expect($periodo['fim'])->not->toBeNull();
});
