<?php

use App\Enums\StatusContrato;
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

    $this->tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Cadeira',
    ]);

    $this->lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'L001',
        'quantidade_total' => 100,
        'quantidade_disponivel' => 30,
        'valor_total' => 10000.00,
        'valor_frete' => 500.00,
        'data_aquisicao' => Carbon::now()->subYear(),
    ]);
});

test('retorna resumo de rentabilidade do lote', function () {
    // Cria um contrato ativo com itens e pagamentos
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
        'data_inicio' => Carbon::now()->subMonths(3),
        'data_termino' => Carbon::now()->addMonths(3),
    ]);

    $item = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 50,
        'valor_unitario' => 10.00,
    ]);

    // Aloca unidades do lote no item
    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 50,
    ]);

    // Cria pagamentos pagos
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1500.00,
        'data_pagamento' => Carbon::now()->subMonths(2),
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1500.00,
        'data_pagamento' => Carbon::now()->subMonth(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'lote' => ['id', 'codigo', 'quantidade_total', 'quantidade_disponivel'],
        'resumo' => [
            'custo_aquisicao',
            'total_recebido',
            'roi_percentual',
            'unidades_alocadas',
            'contratos_count',
        ],
        'pagamentos_por_mes',
        'ocupacao_por_mes',
    ]);
});

test('calcula receita proporcional por lote', function () {
    // Contrato com item que usa 2 lotes diferentes
    $lote2 = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'L002',
        'quantidade_total' => 50,
        'quantidade_disponivel' => 0,
    ]);

    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
    ]);

    $item = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 100, // Total de 100 unidades
    ]);

    // Lote 1: 70 unidades (70%)
    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 70,
    ]);

    // Lote 2: 30 unidades (30%)
    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $lote2->id,
        'quantidade_alocada' => 30,
    ]);

    // Pagamento de 1000
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::now()->subMonth(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);

    // Lote 1 deve receber 70% do pagamento = 700
    $resumo = $response->json('resumo');
    expect((float) $resumo['total_recebido'])->toBe(700.00);
});

test('retorna evolucao de pagamentos por mes', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
    ]);

    $item = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 50,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 50,
    ]);

    // Pagamentos em meses diferentes
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(5),
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1500.00,
        'data_pagamento' => Carbon::now()->subMonth()->startOfMonth()->addDays(10),
    ]);

    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 2000.00,
        'data_pagamento' => Carbon::now()->startOfMonth()->addDays(3),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);

    $pagamentosPorMes = $response->json('pagamentos_por_mes');
    expect($pagamentosPorMes)->toBeArray();
    expect(count($pagamentosPorMes))->toBeGreaterThanOrEqual(3);

    // Verifica que cada item tem mes e valor
    foreach ($pagamentosPorMes as $item) {
        expect($item)->toHaveKeys(['mes', 'valor']);
    }
});

test('retorna ocupacao dos ultimos 24 meses', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);

    $ocupacao = $response->json('ocupacao_por_mes');
    expect($ocupacao)->toBeArray();
    expect(count($ocupacao))->toBe(24);

    // Verifica estrutura
    foreach ($ocupacao as $item) {
        expect($item)->toHaveKeys(['mes', 'percentual']);
        expect($item['percentual'])->toBeGreaterThanOrEqual(0);
        expect($item['percentual'])->toBeLessThanOrEqual(100);
    }
});

test('calcula ROI corretamente', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
    ]);

    $item = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 100,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 100,
    ]);

    // Custo: 10500 (10000 + 500)
    // Receita: 5250 (50% do custo)
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 5250.00,
        'data_pagamento' => Carbon::now()->subMonth(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);

    $resumo = $response->json('resumo');

    // ROI = (receita / custo) * 100 = (5250 / 10500) * 100 = 50%
    expect((float) $resumo['custo_aquisicao'])->toBe(10500.00);
    expect((float) $resumo['total_recebido'])->toBe(5250.00);
    expect((float) $resumo['roi_percentual'])->toBe(50.00);
});

test('ignora pagamentos pendentes no calculo', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
    ]);

    $item = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 100,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 100,
    ]);

    // Pagamento pago
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'data_pagamento' => Carbon::now()->subMonth(),
    ]);

    // Pagamento pendente - nao deve contar
    Pagamento::factory()->pendente()->create([
        'contrato_id' => $contrato->id,
        'valor' => 5000.00,
    ]);

    // Pagamento atrasado - nao deve contar
    Pagamento::factory()->atrasado()->create([
        'contrato_id' => $contrato->id,
        'valor' => 3000.00,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);

    // Apenas o pagamento pago deve ser considerado
    $resumo = $response->json('resumo');
    expect((float) $resumo['total_recebido'])->toBe(1000.00);
});

test('retorna 403 para lote de outro locador', function () {
    // Cria outro locador e lote
    $outroLocador = Pessoa::factory()->locador()->create();
    $outroTipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $outroLocador->id,
    ]);
    $loteDiferente = Lote::factory()->create([
        'locador_id' => $outroLocador->id,
        'tipo_ativo_id' => $outroTipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$loteDiferente->id}/rentabilidade");

    $response->assertStatus(403);
});

test('retorna 404 para lote inexistente', function () {
    $uuidInexistente = '00000000-0000-0000-0000-000000000000';

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$uuidInexistente}/rentabilidade");

    $response->assertStatus(404);
});

test('calcula ocupacao baseada em contratos ativos e finalizados', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    // Contrato ativo no mes atual
    $contratoAtivo = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
        'data_inicio' => Carbon::now()->subMonth(),
        'data_termino' => Carbon::now()->addMonth(),
    ]);

    $itemAtivo = ContratoItem::factory()->create([
        'contrato_id' => $contratoAtivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 50,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $itemAtivo->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 50,
    ]);

    // Contrato finalizado
    $contratoFinalizado = Contrato::factory()->finalizado()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
        'data_inicio' => Carbon::now()->subMonths(4),
        'data_termino' => Carbon::now()->subMonths(2),
    ]);

    $itemFinalizado = ContratoItem::factory()->create([
        'contrato_id' => $contratoFinalizado->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 30,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $itemFinalizado->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 30,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);

    // Verifica que temos ocupacao nos meses apropriados
    $ocupacao = $response->json('ocupacao_por_mes');
    $ocupacaoComValor = array_filter($ocupacao, fn($item) => $item['percentual'] > 0);

    expect(count($ocupacaoComValor))->toBeGreaterThanOrEqual(1);
});

test('considera desconto comercial no valor final do pagamento', function () {
    $locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $locatario->id,
    ]);

    $item = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 100,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $item->id,
        'lote_id' => $this->lote->id,
        'quantidade_alocada' => 100,
    ]);

    // Pagamento com desconto: 1000 - 100 = 900 valor_final
    Pagamento::factory()->pago()->create([
        'contrato_id' => $contrato->id,
        'valor' => 1000.00,
        'desconto_comercial' => 100.00,
        'data_pagamento' => Carbon::now()->subMonth(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/lotes/{$this->lote->id}/rentabilidade");

    $response->assertStatus(200);

    // Deve considerar o valor_final (900), nao o valor bruto
    $resumo = $response->json('resumo');
    expect((float) $resumo['total_recebido'])->toBe(900.00);
});
