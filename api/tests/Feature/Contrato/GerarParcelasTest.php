<?php

use App\Enums\StatusContrato;
use App\Enums\TipoCobranca;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Lote;
use App\Models\Pagamento;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(),
    ]);
    $this->locatario = Pessoa::factory()->locatario()->create();
    $this->locatario->locador()->associate($this->locador);
    $this->locatario->save();

    $this->user = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);

    $this->tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
    ]);

    Lote::factory()->comQuantidade(100)->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
    ]);
});

// =================================================================
// GERACAO AUTOMATICA DE PARCELAS
// =================================================================

test('gera parcelas automaticamente para contrato recorrente manual', function () {
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
        'data_inicio' => '2026-03-15',
        'data_termino' => '2026-05-14', // Marco a Maio = 3 meses
        'valor_total' => 3000.00,
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/pagamentos/gerar-automaticamente");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Parcelas geradas com sucesso.',
            'data' => [
                'total_parcelas' => 3,
                'valor_parcela' => 1000.00,
            ],
        ]);

    // Verifica as parcelas criadas
    expect($contrato->pagamentos()->count())->toBe(3);

    $parcelas = $contrato->pagamentos()->orderBy('data_vencimento')->get();

    // Parcela 1: 15/03/2026
    expect($parcelas[0]->data_vencimento->format('Y-m-d'))->toBe('2026-03-15');
    expect((float) $parcelas[0]->valor)->toBe(1000.00);

    // Parcela 2: 15/04/2026
    expect($parcelas[1]->data_vencimento->format('Y-m-d'))->toBe('2026-04-15');

    // Parcela 3: 15/05/2026
    expect($parcelas[2]->data_vencimento->format('Y-m-d'))->toBe('2026-05-15');
});

test('ajusta dia de vencimento quando mes nao tem o dia', function () {
    // Contrato comeca dia 31
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
        'data_inicio' => '2026-01-31', // Dia 31
        'data_termino' => '2026-04-30', // 4 meses
        'valor_total' => 4000.00,
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/pagamentos/gerar-automaticamente");

    $response->assertStatus(200);

    $parcelas = $contrato->pagamentos()->orderBy('data_vencimento')->get();

    // Janeiro: 31
    expect($parcelas[0]->data_vencimento->format('Y-m-d'))->toBe('2026-01-31');

    // Fevereiro: 28 (nao tem dia 31)
    expect($parcelas[1]->data_vencimento->format('Y-m-d'))->toBe('2026-02-28');

    // Marco: 31
    expect($parcelas[2]->data_vencimento->format('Y-m-d'))->toBe('2026-03-31');

    // Abril: 30 (nao tem dia 31)
    expect($parcelas[3]->data_vencimento->format('Y-m-d'))->toBe('2026-04-30');
});

test('nao permite gerar parcelas para contrato nao recorrente manual', function () {
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::SemCobranca,
        'valor_total' => 3000.00,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/pagamentos/gerar-automaticamente");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Geracao automatica de parcelas so e permitida para contratos com cobranca recorrente manual.',
        ]);
});

test('nao permite gerar parcelas para contrato que ja possui parcelas', function () {
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
        'valor_total' => 3000.00,
    ]);

    // Cria uma parcela manualmente
    $pagamento = new Pagamento([
        'valor' => 1000.00,
        'data_vencimento' => now()->addMonth(),
        'status' => 'pendente',
        'origem' => 'manual',
    ]);
    $pagamento->contrato()->associate($contrato);
    $pagamento->save();

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/pagamentos/gerar-automaticamente");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'O contrato ja possui parcelas cadastradas.',
        ]);
});

test('nao permite gerar parcelas para contrato ativo', function () {
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Ativo,
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
        'valor_total' => 3000.00,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/pagamentos/gerar-automaticamente");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Parcelas so podem ser geradas para contratos em rascunho.',
        ]);
});

// =================================================================
// VALIDACAO DE PARCELAS NA ATIVACAO
// =================================================================

test('nao permite ativar contrato recorrente manual sem parcelas', function () {
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
        'valor_total' => 3000.00,
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/ativar");

    $response->assertStatus(422)
        ->assertJson([
            'error_type' => 'contrato_sem_parcelas',
        ]);
});

test('permite ativar contrato recorrente manual com parcelas', function () {
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::RecorrenteManual,
        'valor_total' => 3000.00,
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    // Adiciona uma parcela
    $pagamento = new Pagamento([
        'valor' => 3000.00,
        'data_vencimento' => now()->addMonth(),
        'status' => 'pendente',
        'origem' => 'manual',
    ]);
    $pagamento->contrato()->associate($contrato);
    $pagamento->save();

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/ativar");

    $response->assertStatus(200);

    $contrato->refresh();
    expect($contrato->status)->toBe(StatusContrato::Ativo);
});

test('contrato sem cobranca nao exige parcelas para ativar', function () {
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => StatusContrato::Rascunho,
        'tipo_cobranca' => TipoCobranca::SemCobranca,
        'valor_total' => 3000.00,
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$contrato->id}/ativar");

    $response->assertStatus(200);

    $contrato->refresh();
    expect($contrato->status)->toBe(StatusContrato::Ativo);
});
