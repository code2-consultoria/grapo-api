<?php

use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->locador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(), // Assinatura ativa
    ]);
    $this->user = User::factory()->create();
    VinculoTime::factory()->create([
        'user_id' => $this->user->id,
        'locador_id' => $this->locador->id,
    ]);
});

describe('Dashboard - Autenticacao', function () {
    test('usuario nao autenticado nao pode acessar dashboard', function () {
        $response = $this->getJson('/api/dashboard');

        $response->assertUnauthorized();
    });

    test('usuario sem locador recebe erro 403', function () {
        $userSemLocador = User::factory()->create();
        Sanctum::actingAs($userSemLocador);

        $response = $this->getJson('/api/dashboard');

        $response->assertForbidden();
        $response->assertJsonPath('message', 'Usuario nao vinculado a um locador.');
    });
});

describe('Dashboard - Metricas Financeiras', function () {
    test('retorna receita total de contratos ativos', function () {
        Sanctum::actingAs($this->user);

        $locatario = Pessoa::factory()->locatario()->create();
        $locatario->locador()->associate($this->locador);
        $locatario->save();

        // Cria 2 contratos ativos
        Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create(['status' => 'ativo', 'valor_total' => 1000.00]);

        Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create(['status' => 'ativo', 'valor_total' => 500.00]);

        // Cria 1 contrato cancelado (nao deve contar)
        Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create(['status' => 'cancelado', 'valor_total' => 2000.00]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $receita = $response->json('data.financeiro.receita_total');
        expect((float) $receita)->toBe(1500.0);
        $response->assertJsonPath('data.financeiro.contratos_ativos', 2);
    });

    test('retorna contratos a vencer nos proximos 30 dias', function () {
        Sanctum::actingAs($this->user);

        $locatario = Pessoa::factory()->locatario()->create(['nome' => 'David']);
        $locatario->locador()->associate($this->locador);
        $locatario->save();

        // Contrato vencendo em 15 dias
        Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create([
                'status' => 'ativo',
                'data_termino' => Carbon::now()->addDays(15),
                'codigo' => 'CTR-0001',
            ]);

        // Contrato vencendo em 45 dias (nao deve aparecer)
        Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create([
                'status' => 'ativo',
                'data_termino' => Carbon::now()->addDays(45),
            ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $contratos = $response->json('data.financeiro.contratos_a_vencer');
        expect($contratos)->toHaveCount(1);
        expect($contratos[0]['codigo'])->toBe('CTR-0001');
        expect($contratos[0]['locatario'])->toBe('David');
    });
});

describe('Dashboard - Metricas Operacionais', function () {
    test('retorna estoque total e disponivel', function () {
        Sanctum::actingAs($this->user);

        $tipoAtivo = TipoAtivo::factory()
            ->for($this->locador, 'locador')
            ->create();

        Lote::factory()
            ->for($this->locador, 'locador')
            ->for($tipoAtivo, 'tipoAtivo')
            ->create([
                'quantidade_total' => 100,
                'quantidade_disponivel' => 60,
            ]);

        Lote::factory()
            ->for($this->locador, 'locador')
            ->for($tipoAtivo, 'tipoAtivo')
            ->create([
                'quantidade_total' => 50,
                'quantidade_disponivel' => 50,
            ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.operacional.estoque_total', 150);
        $response->assertJsonPath('data.operacional.estoque_disponivel', 110);
        $response->assertJsonPath('data.operacional.estoque_alocado', 40);
        $response->assertJsonPath('data.operacional.taxa_ocupacao', 26.7);
    });

    test('retorna lotes por status', function () {
        Sanctum::actingAs($this->user);

        $tipoAtivo = TipoAtivo::factory()
            ->for($this->locador, 'locador')
            ->create();

        Lote::factory()
            ->for($this->locador, 'locador')
            ->for($tipoAtivo, 'tipoAtivo')
            ->create(['status' => 'disponivel']);

        Lote::factory()
            ->for($this->locador, 'locador')
            ->for($tipoAtivo, 'tipoAtivo')
            ->create(['status' => 'disponivel']);

        Lote::factory()
            ->for($this->locador, 'locador')
            ->for($tipoAtivo, 'tipoAtivo')
            ->create(['status' => 'esgotado']);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.operacional.lotes_por_status.disponivel', 2);
        $response->assertJsonPath('data.operacional.lotes_por_status.esgotado', 1);
    });

    test('retorna top 5 ativos mais alugados', function () {
        Sanctum::actingAs($this->user);

        $locatario = Pessoa::factory()->locatario()->create();
        $locatario->locador()->associate($this->locador);
        $locatario->save();

        $tipoAtivo1 = TipoAtivo::factory()
            ->for($this->locador, 'locador')
            ->create(['nome' => 'Tatame EVA']);

        $tipoAtivo2 = TipoAtivo::factory()
            ->for($this->locador, 'locador')
            ->create(['nome' => 'Cadeira']);

        $contrato = Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create(['status' => 'ativo']);

        ContratoItem::factory()
            ->for($contrato, 'contrato')
            ->for($tipoAtivo1, 'tipoAtivo')
            ->create(['quantidade' => 50]);

        ContratoItem::factory()
            ->for($contrato, 'contrato')
            ->for($tipoAtivo2, 'tipoAtivo')
            ->create(['quantidade' => 20]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $topAtivos = $response->json('data.operacional.top_ativos');
        expect($topAtivos)->toHaveCount(2);
        expect($topAtivos[0]['nome'])->toBe('Tatame EVA');
        expect($topAtivos[0]['quantidade'])->toBe(50);
    });
});

describe('Dashboard - Alertas', function () {
    test('retorna alerta de contratos vencendo em 7 dias', function () {
        Sanctum::actingAs($this->user);

        $locatario = Pessoa::factory()->locatario()->create();
        $locatario->locador()->associate($this->locador);
        $locatario->save();

        Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create([
                'status' => 'ativo',
                'data_termino' => Carbon::now()->addDays(5),
            ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $alertas = $response->json('data.alertas');
        $alertaVencimento = collect($alertas)->firstWhere('titulo', 'Contratos a vencer');

        expect($alertaVencimento)->not->toBeNull();
        expect($alertaVencimento['tipo'])->toBe('warning');
    });

    test('retorna alerta de estoque baixo', function () {
        Sanctum::actingAs($this->user);

        $tipoAtivo = TipoAtivo::factory()
            ->for($this->locador, 'locador')
            ->create(['nome' => 'Cadeira Plastica']);

        Lote::factory()
            ->for($this->locador, 'locador')
            ->for($tipoAtivo, 'tipoAtivo')
            ->create([
                'quantidade_total' => 10,
                'quantidade_disponivel' => 3,
            ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $alertas = $response->json('data.alertas');
        $alertaEstoque = collect($alertas)->firstWhere('titulo', 'Estoque baixo');

        expect($alertaEstoque)->not->toBeNull();
        expect($alertaEstoque['tipo'])->toBe('info');
    });

    test('retorna alerta de ativos esgotados', function () {
        Sanctum::actingAs($this->user);

        $tipoAtivo = TipoAtivo::factory()
            ->for($this->locador, 'locador')
            ->create(['nome' => 'Mesa']);

        Lote::factory()
            ->for($this->locador, 'locador')
            ->for($tipoAtivo, 'tipoAtivo')
            ->create([
                'quantidade_total' => 10,
                'quantidade_disponivel' => 0,
            ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $alertas = $response->json('data.alertas');
        $alertaEsgotado = collect($alertas)->firstWhere('titulo', 'Ativos esgotados');

        expect($alertaEsgotado)->not->toBeNull();
        expect($alertaEsgotado['tipo'])->toBe('destructive');
    });
});

describe('Dashboard - Multi-tenant', function () {
    test('usuario ve apenas metricas do seu locador', function () {
        Sanctum::actingAs($this->user);

        // Dados do locador do usuario
        $locatario = Pessoa::factory()->locatario()->create();
        $locatario->locador()->associate($this->locador);
        $locatario->save();

        Contrato::factory()
            ->for($this->locador, 'locador')
            ->for($locatario, 'locatario')
            ->create(['status' => 'ativo', 'valor_total' => 1000.00]);

        // Dados de outro locador
        $outroLocador = Pessoa::factory()->locador()->create();
        $outroLocatario = Pessoa::factory()->locatario()->create();
        $outroLocatario->locador()->associate($outroLocador);
        $outroLocatario->save();

        Contrato::factory()
            ->for($outroLocador, 'locador')
            ->for($outroLocatario, 'locatario')
            ->create(['status' => 'ativo', 'valor_total' => 5000.00]);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        // Deve ver apenas a receita do seu locador
        $receita = $response->json('data.financeiro.receita_total');
        expect((float) $receita)->toBe(1000.0);
        $response->assertJsonPath('data.financeiro.contratos_ativos', 1);
    });
});
