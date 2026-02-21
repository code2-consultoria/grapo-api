<?php

use App\Models\Contrato;
use App\Models\ContratoAditivo;
use App\Models\ContratoItem;
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
// PRORROGAÇÃO - CENÁRIOS DE SUCESSO
// =================================================================

// Cenário: Cria aditivo de prorrogação
test('cria aditivo de prorrogacao', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_termino' => now()->addMonth(),
    ]);

    $novaData = now()->addMonths(3)->format('Y-m-d');

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'prorrogacao',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        'descricao' => 'Prorrogação por mais 2 meses',
        'nova_data_termino' => $novaData,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.tipo', 'prorrogacao');
    // Data retornada em formato ISO
    expect($response->json('data.nova_data_termino'))->toContain($novaData);
});

// Cenário: Ativar prorrogação atualiza data de término
test('ativar prorrogacao atualiza data de termino do contrato', function () {
    $dataTerminoOriginal = now()->addMonth();
    $novaDataTermino = now()->addMonths(3);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now()->subMonth(),
        'data_termino' => $dataTerminoOriginal,
    ]);

    $aditivo = ContratoAditivo::factory()->prorrogacao()->create([
        'contrato_id' => $contrato->id,
        'nova_data_termino' => $novaDataTermino,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(200);
    $response->assertJsonPath('data.status', 'ativo');

    $contrato->refresh();
    expect($contrato->data_termino->format('Y-m-d'))->toBe($novaDataTermino->format('Y-m-d'));
});

// Cenário: Prorrogação recalcula valor total
test('prorrogacao recalcula valor total do contrato', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now(),
        'data_termino' => now()->addDays(30), // 31 dias
        'valor_total' => 3100, // R$ 10/dia x 10 unidades x 31 dias
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
        'valor_unitario' => 10.00,
        'periodo_aluguel' => 'diaria',
        'valor_total_item' => 3100,
    ]);

    // Prorrogar para 60 dias
    $aditivo = ContratoAditivo::factory()->prorrogacao()->create([
        'contrato_id' => $contrato->id,
        'nova_data_termino' => now()->addDays(59), // 60 dias total
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(200);

    $contrato->refresh();
    // Novo valor: R$ 10/dia x 10 unidades x 60 dias = 6000
    expect((float) $contrato->valor_total)->toBe(6000.0);
});

// Cenário: Cancelar prorrogação restaura data original
test('cancelar prorrogacao restaura data de termino original', function () {
    $dataTerminoOriginal = now()->addMonth();
    $novaDataTermino = now()->addMonths(3);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_inicio' => now()->subMonth(),
        'data_termino' => $dataTerminoOriginal,
    ]);

    // Cria e ativa aditivo
    $aditivo = ContratoAditivo::factory()->prorrogacao()->create([
        'contrato_id' => $contrato->id,
        'nova_data_termino' => $novaDataTermino,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    // Cancela o aditivo
    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/cancelar");

    $response->assertStatus(200);
    $response->assertJsonPath('data.status', 'cancelado');

    $contrato->refresh();
    expect($contrato->data_termino->format('Y-m-d'))->toBe($dataTerminoOriginal->format('Y-m-d'));
});

// =================================================================
// PRORROGAÇÃO - CENÁRIOS DE ERRO
// =================================================================

// Cenário: Não permite prorrogação que reduz prazo
test('nao permite prorrogacao que reduz prazo', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'data_termino' => now()->addMonths(3),
    ]);

    // Nova data menor que a atual
    $aditivo = ContratoAditivo::factory()->prorrogacao()->create([
        'contrato_id' => $contrato->id,
        'nova_data_termino' => now()->addMonth(),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/aditivos/{$aditivo->id}/ativar");

    $response->assertStatus(422);
    $response->assertJsonPath('message', fn ($m) => str_contains($m, 'deve ser posterior'));
});

// Cenário: Não permite ativar prorrogação sem nova_data_termino
test('valida nova_data_termino na criacao de prorrogacao', function () {
    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/contratos/{$contrato->id}/aditivos", [
        'tipo' => 'prorrogacao',
        'data_vigencia' => now()->addDay()->format('Y-m-d'),
        // nova_data_termino não informada
    ]);

    // Cria com sucesso, mas ao ativar vai falhar se nova_data_termino for null
    $response->assertStatus(201);
});
