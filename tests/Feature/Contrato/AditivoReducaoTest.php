<?php

use App\Models\AlocacaoLote;
use App\Models\Contrato;
use App\Models\ContratoAditivo;
use App\Models\ContratoAditivoItem;
use App\Models\ContratoItem;
use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(),
    ]);
    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);
    $this->locatario = Pessoa::factory()->locatario()->create([
        'locador_id' => $this->locador->id,
    ]);
    $this->tipoAtivo = TipoAtivo::factory()->placaEva()->create([
        'locador_id' => $this->locador->id,
    ]);
});

// =================================================================
// REDUÇÃO - CENÁRIOS DE SUCESSO
// =================================================================

// Cenário: Cria aditivo de redução com item negativo
test('cria aditivo de reducao com item negativo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'reducao',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'descricao' => 'Redução de 5 placas',
    ]);

    $response->assertStatus(201);
    $aditivo = ContratoAditivo::find($response->json('data.id'));

    // Adiciona item com quantidade negativa
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade_alterada' => -5, // Negativo para redução
            'valor_unitario' => 10.00,
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.quantidade_alterada', -5);
});

// Cenário: Ativar redução libera lotes usando LIFO
test('ativar reducao libera lotes usando LIFO', function () {
    // Cria lotes
    $loteAntigo = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-ANTIGO',
        'quantidade_total' => 10,
        'quantidade_disponivel' => 0, // Todo alocado
        'data_aquisicao' => now()->subYear(),
    ]);

    $loteRecente = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-RECENTE',
        'quantidade_total' => 10,
        'quantidade_disponivel' => 5, // 5 alocados
        'data_aquisicao' => now()->subMonth(),
    ]);

    // Cria contrato ativo com alocações
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now(),
        'data_termino' => now()->addDays(29),
        'valor_total' => 4500, // 15 x 10 x 30
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 15,
        'valor_unitario' => 10.00,
        'valor_total_item' => 4500,
    ]);

    // Cria alocações (FIFO: 10 do antigo + 5 do recente)
    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $loteAntigo->id,
        'quantidade_alocada' => 10,
        'created_at' => now()->subHour(), // Alocado primeiro
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $loteRecente->id,
        'quantidade_alocada' => 5,
        'created_at' => now(), // Alocado depois
    ]);

    // Cria aditivo de redução
    $aditivo = ContratoAditivo::factory()->reducao()->create([
        'contrato_id' => $contrato->id,
    ]);

    // Reduzir 7 unidades (deve liberar primeiro do lote recente - LIFO)
    ContratoAditivoItem::factory()->reducao(7)->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    // Ativa o aditivo
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(200);

    // Verifica liberação LIFO
    $loteAntigo->refresh();
    $loteRecente->refresh();
    // LIFO: libera primeiro do recente (5), depois do antigo (2)
    expect($loteRecente->quantidade_disponivel)->toBe(10); // Liberou todos os 5
    expect($loteAntigo->quantidade_disponivel)->toBe(2); // Liberou 2
});

// Cenário: Redução atualiza valor do contrato
test('reducao diminui valor total do contrato', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 10, // 10 alocados
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now(),
        'data_termino' => now()->addDays(29), // 30 dias
        'valor_total' => 3000, // 10 x 10 x 30
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
        'valor_unitario' => 10.00,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $lote->id,
        'quantidade_alocada' => 10,
    ]);

    $aditivo = ContratoAditivo::factory()->reducao()->create([
        'contrato_id' => $contrato->id,
    ]);

    // Redução: -5 unidades x R$ 10/dia x 30 dias = -R$ 1500
    ContratoAditivoItem::factory()->reducao(5)->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $contrato->refresh();
    // Valor original 3000 - redução 1500 = 1500
    expect((float) $contrato->valor_total)->toBe(1500.0);
});

// Cenário: Redução atualiza quantidade do item no contrato
test('reducao atualiza quantidade do item no contrato', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 10,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $lote->id,
        'quantidade_alocada' => 10,
    ]);

    $aditivo = ContratoAditivo::factory()->reducao()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->reducao(3)->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $contratoItem->refresh();
    expect($contratoItem->quantidade)->toBe(7); // 10 - 3
});

// =================================================================
// REDUÇÃO - CENÁRIOS DE ERRO
// =================================================================

// Cenário: Erro se redução excede quantidade alocada
test('reducao falha se exceder quantidade alocada', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 15, // Só 5 alocados
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 5,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $lote->id,
        'quantidade_alocada' => 5,
    ]);

    $aditivo = ContratoAditivo::factory()->reducao()->create([
        'contrato_id' => $contrato->id,
    ]);

    // Tenta reduzir mais do que alocado
    ContratoAditivoItem::factory()->reducao(10)->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'Não é possível reduzir'));
});

// Cenário: Erro se tipo de ativo não existe no contrato
test('reducao falha se tipo de ativo nao existir no contrato', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    // Contrato não tem itens deste tipo

    $aditivo = ContratoAditivo::factory()->reducao()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->reducao(5)->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'Não é possível reduzir'));
});

// Cenário: Cancelar redução realoca itens
test('cancelar reducao realoca itens liberados', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 10, // 10 alocados
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 0,
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $lote->id,
        'quantidade_alocada' => 10,
    ]);

    $aditivo = ContratoAditivo::factory()->reducao()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->reducao(5)->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);

    // Ativa
    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(15); // 5 liberados

    $contratoItem->refresh();
    expect($contratoItem->quantidade)->toBe(5);

    // Cancela
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/cancelar");

    $response->assertStatus(200);

    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(10); // Realocados

    $contratoItem->refresh();
    expect($contratoItem->quantidade)->toBe(10); // Restaurado
});
