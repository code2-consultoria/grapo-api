<?php

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
// ACRÉSCIMO - CENÁRIOS DE SUCESSO
// =================================================================

// Cenário: Cria aditivo de acréscimo com item
test('cria aditivo de acrescimo com item', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    // Cria aditivo
    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'acrescimo',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'descricao' => 'Acréscimo de 5 placas',
    ]);

    $response->assertStatus(201);
    $aditivo = ContratoAditivo::find($response->json('data.id'));

    // Adiciona item
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/itens", [
            'tipo_ativo_id' => $this->tipoAtivo->id,
            'quantidade_alterada' => 5,
            'valor_unitario' => 10.00,
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.quantidade_alterada', 5);
});

// Cenário: Ativar acréscimo aloca lotes usando FIFO
test('ativar acrescimo aloca lotes usando FIFO', function () {
    // Cria lotes
    $loteAntigo = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-ANTIGO',
        'quantidade_total' => 10,
        'quantidade_disponivel' => 10,
        'data_aquisicao' => now()->subYear(),
    ]);

    $loteRecente = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-RECENTE',
        'quantidade_total' => 10,
        'quantidade_disponivel' => 10,
        'data_aquisicao' => now()->subMonth(),
    ]);

    // Cria contrato ativo
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now(),
        'data_termino' => now()->addDays(29), // 30 dias
        'valor_total' => 0,
    ]);

    // Cria aditivo de acréscimo
    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_alterada' => 12, // Vai pegar 10 do antigo + 2 do recente
        'valor_unitario' => 5.00,
    ]);

    // Ativa o aditivo
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(200);

    // Verifica alocação FIFO
    $loteAntigo->refresh();
    $loteRecente->refresh();
    expect($loteAntigo->quantidade_disponivel)->toBe(0); // Todo alocado
    expect($loteRecente->quantidade_disponivel)->toBe(8); // 2 alocados
});

// Cenário: Acréscimo atualiza valor do contrato
test('acrescimo atualiza valor total do contrato', function () {
    // Cria lote com disponibilidade
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 20,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now(),
        'data_termino' => now()->addDays(29), // 30 dias
        'valor_total' => 1000,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    // Acréscimo: 5 unidades x R$ 10/dia x 30 dias = R$ 1500
    ContratoAditivoItem::factory()->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_alterada' => 5,
        'valor_unitario' => 10.00,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $contrato->refresh();
    // Valor original 1000 + acréscimo 1500 = 2500
    expect((float) $contrato->valor_total)->toBe(2500.0);
});

// Cenário: Acréscimo cria item no contrato se não existir
test('acrescimo cria item no contrato se tipo ativo nao existir', function () {
    // Cria lote
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 20,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 0,
    ]);

    // Contrato não tem itens
    expect($contrato->itens()->count())->toBe(0);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_alterada' => 5,
        'valor_unitario' => 10.00,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    // Verifica que item foi criado
    expect($contrato->itens()->count())->toBe(1);
    expect($contrato->itens()->first()->quantidade)->toBe(5);
});

// Cenário: Acréscimo incrementa quantidade se item já existir
test('acrescimo incrementa quantidade se item ja existir', function () {
    // Cria lote
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 30,
        'quantidade_disponivel' => 30,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    // Contrato já tem 10 unidades deste tipo
    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_alterada' => 5,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    // Verifica que quantidade foi incrementada
    $item = $contrato->itens()->where('tipo_ativo_id', $this->tipoAtivo->id)->first();
    expect($item->quantidade)->toBe(15); // 10 + 5
});

// =================================================================
// ACRÉSCIMO - CENÁRIOS DE ERRO
// =================================================================

// Cenário: Erro se não houver disponibilidade
test('acrescimo falha se nao houver disponibilidade', function () {
    // Lote com pouca disponibilidade
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 5,
        'quantidade_disponivel' => 5,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    // Pede mais do que disponível
    ContratoAditivoItem::factory()->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_alterada' => 20,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'unidades disponíveis'));
});

// Cenário: Cancelar acréscimo libera itens alocados
test('cancelar acrescimo libera itens alocados', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 20,
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 0,
    ]);

    $aditivo = ContratoAditivo::factory()->acrescimo()->create([
        'contrato_id' => $contrato->id,
    ]);

    ContratoAditivoItem::factory()->create([
        'contrato_aditivo_id' => $aditivo->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_alterada' => 10,
        'valor_unitario' => 5.00,
    ]);

    // Ativa
    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(10); // 10 alocados

    // Cancela
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/cancelar");

    $response->assertStatus(200);

    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(20); // Devolvidos
});
