<?php

use App\Actions\Alocacao\Alocar;
use App\Actions\Alocacao\Liberar;
use App\Exceptions\QuantidadeIndisponivelException;
use App\Models\AlocacaoLote;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create();
    $this->tipoAtivo = TipoAtivo::factory()->create([
        'locador_id' => $this->locador->id,
        'nome' => 'Placa de EVA',
    ]);
    $this->locatario = Pessoa::factory()->locatario()->create();
});

// Cenário: Alocação FIFO com múltiplos lotes
test('aloca itens usando FIFO (lote mais antigo primeiro)', function () {
    // Lote A (mais antigo) com 12 unidades
    $loteA = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-A',
        'quantidade_total' => 12,
        'quantidade_disponivel' => 12,
        'data_aquisicao' => now()->subYear(),
        'created_at' => now()->subYear(),
    ]);

    // Lote B (mais recente) com 10 unidades
    $loteB = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'codigo' => 'LOTE-B',
        'quantidade_total' => 10,
        'quantidade_disponivel' => 10,
        'data_aquisicao' => now()->subMonth(),
        'created_at' => now()->subMonth(),
    ]);

    // Contrato solicitando 15 unidades
    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => 'rascunho',
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 15,
        'valor_unitario_diaria' => 5.00,
        'valor_total_item' => 15 * 5.00 * 30,
    ]);

    // Executa a alocação
    $alocar = new Alocar($contratoItem);
    $alocar->handle();

    // Verifica alocações criadas
    $alocacoes = AlocacaoLote::where('contrato_item_id', $contratoItem->id)->get();
    expect($alocacoes)->toHaveCount(2);

    // Verifica que alocou 12 do Lote A (mais antigo)
    $alocacaoA = $alocacoes->where('lote_id', $loteA->id)->first();
    expect($alocacaoA->quantidade_alocada)->toBe(12);

    // Verifica que alocou 3 do Lote B
    $alocacaoB = $alocacoes->where('lote_id', $loteB->id)->first();
    expect($alocacaoB->quantidade_alocada)->toBe(3);

    // Verifica disponibilidade atualizada dos lotes
    $loteA->refresh();
    $loteB->refresh();
    expect($loteA->quantidade_disponivel)->toBe(0);
    expect($loteB->quantidade_disponivel)->toBe(7);
});

// Cenário: Alocação de lote único
test('aloca itens de um único lote quando há disponibilidade suficiente', function () {
    $lote = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 20,
    ]);

    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => 'rascunho',
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
        'valor_unitario_diaria' => 5.00,
        'valor_total_item' => 10 * 5.00 * 30,
    ]);

    $alocar = new Alocar($contratoItem);
    $alocar->handle();

    $alocacoes = AlocacaoLote::where('contrato_item_id', $contratoItem->id)->get();
    expect($alocacoes)->toHaveCount(1);
    expect($alocacoes->first()->quantidade_alocada)->toBe(10);

    $lote->refresh();
    expect($lote->quantidade_disponivel)->toBe(10);
});

// Cenário: Erro quando não há disponibilidade suficiente
test('lança exceção quando quantidade indisponível', function () {
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 10,
        'quantidade_disponivel' => 10,
    ]);

    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'status' => 'rascunho',
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 30,
        'valor_unitario_diaria' => 5.00,
        'valor_total_item' => 30 * 5.00 * 30,
    ]);

    $alocar = new Alocar($contratoItem);

    expect(fn () => $alocar->handle())
        ->toThrow(QuantidadeIndisponivelException::class);
});

// Cenário: Liberação de itens ao cancelar
test('libera itens alocados corretamente', function () {
    $loteA = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 12,
        'quantidade_disponivel' => 0, // Já alocado
    ]);

    $loteB = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 10,
        'quantidade_disponivel' => 7, // Parcialmente alocado
    ]);

    $contrato = Contrato::factory()->ativo()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 15,
    ]);

    // Cria alocações manualmente
    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $loteA->id,
        'quantidade_alocada' => 12,
    ]);

    AlocacaoLote::factory()->create([
        'contrato_item_id' => $contratoItem->id,
        'lote_id' => $loteB->id,
        'quantidade_alocada' => 3,
    ]);

    // Executa a liberação
    $liberar = new Liberar($contratoItem);
    $liberar->handle();

    // Verifica disponibilidade restaurada
    $loteA->refresh();
    $loteB->refresh();
    expect($loteA->quantidade_disponivel)->toBe(12);
    expect($loteB->quantidade_disponivel)->toBe(10);

    // Verifica que alocações foram removidas
    expect(AlocacaoLote::where('contrato_item_id', $contratoItem->id)->count())->toBe(0);
});

// Cenário: Ignora lotes indisponíveis
test('ignora lotes com status indisponível na alocação', function () {
    // Lote indisponível (mais antigo)
    Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 20,
        'quantidade_disponivel' => 20,
        'status' => 'indisponivel',
        'data_aquisicao' => now()->subYear(),
    ]);

    // Lote disponível
    $loteDisponivel = Lote::factory()->create([
        'locador_id' => $this->locador->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade_total' => 15,
        'quantidade_disponivel' => 15,
        'status' => 'disponivel',
        'data_aquisicao' => now(),
    ]);

    $contrato = Contrato::factory()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
    ]);

    $contratoItem = ContratoItem::factory()->create([
        'contrato_id' => $contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
    ]);

    $alocar = new Alocar($contratoItem);
    $alocar->handle();

    $alocacoes = AlocacaoLote::where('contrato_item_id', $contratoItem->id)->get();
    expect($alocacoes)->toHaveCount(1);
    expect($alocacoes->first()->lote_id)->toBe($loteDisponivel->id);
});
