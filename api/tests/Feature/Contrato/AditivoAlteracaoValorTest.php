<?php

use App\Models\Contrato;
use App\Models\ContratoAditivo;
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
// ALTERAÇÃO DE VALOR - CENÁRIOS DE SUCESSO
// =================================================================

// Cenário: Cria aditivo de alteração de valor positivo
test('cria aditivo de alteracao de valor positivo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'alteracao_valor',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'descricao' => 'Ajuste de valor por serviços adicionais',
        'valor_ajuste' => 500.00,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.tipo', 'alteracao_valor');
    $response->assertJsonPath('data.valor_ajuste', '500.00');
});

// Cenário: Cria aditivo de alteração de valor negativo
test('cria aditivo de alteracao de valor negativo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'alteracao_valor',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'descricao' => 'Desconto comercial',
        'valor_ajuste' => -200.00,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.tipo', 'alteracao_valor');
    $response->assertJsonPath('data.valor_ajuste', '-200.00');
});

// Cenário: Ativar alteração de valor positivo aumenta valor do contrato
test('ativar alteracao de valor positivo aumenta valor do contrato', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 1000.00,
    ]);

    $aditivo = ContratoAditivo::factory()->alteracaoValor(500.00)->create([
        'contrato_id' => $contrato->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(200);
    $response->assertJsonPath('data.status', 'ativo');

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(1500.00);
});

// Cenário: Ativar alteração de valor negativo diminui valor do contrato
test('ativar alteracao de valor negativo diminui valor do contrato', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 1000.00,
    ]);

    $aditivo = ContratoAditivo::factory()->alteracaoValor(-300.00)->create([
        'contrato_id' => $contrato->id,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(700.00);
});

// Cenário: Cancelar alteração de valor restaura valor original
test('cancelar alteracao de valor restaura valor original', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 1000.00,
    ]);

    $aditivo = ContratoAditivo::factory()->alteracaoValor(500.00)->create([
        'contrato_id' => $contrato->id,
    ]);

    // Ativa
    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(1500.00);

    // Cancela
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/cancelar");

    $response->assertStatus(200);

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(1000.00);
});

// Cenário: Cancelar alteração de valor negativo restaura valor original
test('cancelar alteracao de valor negativo restaura valor original', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 1000.00,
    ]);

    $aditivo = ContratoAditivo::factory()->alteracaoValor(-300.00)->create([
        'contrato_id' => $contrato->id,
    ]);

    // Ativa
    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(700.00);

    // Cancela
    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/cancelar");

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(1000.00);
});

// Cenário: Múltiplas alterações de valor
test('multiplas alteracoes de valor acumulam', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 1000.00,
    ]);

    // Primeiro aditivo: +500
    $aditivo1 = ContratoAditivo::factory()->alteracaoValor(500.00)->create([
        'contrato_id' => $contrato->id,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo1->id}/ativar");

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(1500.00);

    // Segundo aditivo: -200
    $aditivo2 = ContratoAditivo::factory()->alteracaoValor(-200.00)->create([
        'contrato_id' => $contrato->id,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo2->id}/ativar");

    $contrato->refresh();
    expect((float) $contrato->valor_total)->toBe(1300.00);
});

// Cenário: Alteração de valor não afeta itens do contrato
test('alteracao de valor nao afeta quantidade de itens', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'valor_total' => 1000.00,
    ]);

    $aditivo = ContratoAditivo::factory()->alteracaoValor(500.00)->create([
        'contrato_id' => $contrato->id,
    ]);

    // Não deve precisar de itens no aditivo
    expect($aditivo->itens()->count())->toBe(0);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(200);
});
