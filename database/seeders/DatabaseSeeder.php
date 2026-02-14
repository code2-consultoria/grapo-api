<?php

namespace Database\Seeders;

use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Documento;
use App\Models\Lote;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar planos padrao
        $this->call(PlanoSeeder::class);

        // Criar locador de teste
        $locador = Pessoa::factory()->locador()->create([
            'nome' => 'Locador Teste LTDA',
            'email' => 'locador@example.com',
        ]);

        // Criar documento do locador (CNPJ)
        Documento::factory()->cnpj()->create([
            'pessoa_id' => $locador->id,
        ]);

        // Criar usuario de teste
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'papel' => 'cliente',
        ]);

        // Vincular usuario ao locador
        VinculoTime::factory()->create([
            'user_id' => $user->id,
            'locador_id' => $locador->id,
        ]);

        // Criar locatario de teste vinculado ao locador
        $locatario = Pessoa::factory()->locatario()->create([
            'nome' => 'Locatario Teste',
            'email' => 'locatario@example.com',
            'locador_id' => $locador->id,
        ]);

        // Criar documento do locatario (CPF)
        Documento::factory()->cpf()->create([
            'pessoa_id' => $locatario->id,
        ]);

        // Criar locatario David
        $david = Pessoa::factory()->locatario()->create([
            'nome' => 'David',
            'email' => 'david@example.com',
            'locador_id' => $locador->id,
        ]);

        // Criar Tipo de Ativo: TATAME EVA
        $tatame = TipoAtivo::create([
            'nome' => 'TATAME EVA 1mX1mX40mm',
            'descricao' => 'para pratica de artes marciais',
            'unidade_medida' => 'u',
            'valor_mensal_sugerido' => 12.50,
            'locador_id' => $locador->id,
        ]);

        // Criar Lotes
        $lote1 = Lote::create([
            'codigo' => '0001',
            'quantidade_total' => 12,
            'quantidade_disponivel' => 0, // Alocados no contrato
            'valor_total' => 1528.80,
            'valor_frete' => 0,
            'fornecedor' => 'Fornecedor A',
            'forma_pagamento' => 'Cartao 4x',
            'status' => 'esgotado',
            'data_aquisicao' => '2025-08-01',
            'tipo_ativo_id' => $tatame->id,
            'locador_id' => $locador->id,
        ]);

        $lote2 = Lote::create([
            'codigo' => '0002',
            'quantidade_total' => 12,
            'quantidade_disponivel' => 0, // Alocados no contrato
            'valor_total' => 1362.00,
            'valor_frete' => 0,
            'fornecedor' => 'Fornecedor B',
            'forma_pagamento' => 'Pix',
            'status' => 'esgotado',
            'data_aquisicao' => '2025-09-01',
            'tipo_ativo_id' => $tatame->id,
            'locador_id' => $locador->id,
        ]);

        $lote3 = Lote::create([
            'codigo' => '0003',
            'quantidade_total' => 18,
            'quantidade_disponivel' => 0, // Alocados no contrato
            'valor_total' => 1881.46,
            'valor_frete' => 0,
            'fornecedor' => 'Fornecedor C',
            'forma_pagamento' => 'Cartao 3x',
            'status' => 'esgotado',
            'data_aquisicao' => '2025-10-01',
            'tipo_ativo_id' => $tatame->id,
            'locador_id' => $locador->id,
        ]);

        // Criar Contratos ativos com David
        // Contrato 1: 12 unidades, total R$ 1.758,12, 12 meses (mensal)
        $contrato1 = Contrato::create([
            'codigo' => 'CTR-0001',
            'data_inicio' => '2025-09-09',
            'data_termino' => '2026-09-09',
            'valor_total' => 1758.12,
            'status' => 'ativo',
            'observacoes' => 'Pagamento: Cartao 4x',
            'locador_id' => $locador->id,
            'locatario_id' => $david->id,
        ]);

        ContratoItem::create([
            'contrato_id' => $contrato1->id,
            'tipo_ativo_id' => $tatame->id,
            'quantidade' => 12,
            'valor_unitario' => 12.21, // 1758.12 / 12 meses / 12 unidades
            'periodo_aluguel' => 'mensal',
            'valor_total_item' => 1758.12,
        ]);

        // Contrato 2: 12 unidades, total R$ 1.566,30, 12 meses (mensal)
        $contrato2 = Contrato::create([
            'codigo' => 'CTR-0002',
            'data_inicio' => '2025-10-20',
            'data_termino' => '2026-10-20',
            'valor_total' => 1566.30,
            'status' => 'ativo',
            'observacoes' => 'Pagamento: Pix',
            'locador_id' => $locador->id,
            'locatario_id' => $david->id,
        ]);

        ContratoItem::create([
            'contrato_id' => $contrato2->id,
            'tipo_ativo_id' => $tatame->id,
            'quantidade' => 12,
            'valor_unitario' => 10.88, // 1566.30 / 12 meses / 12 unidades
            'periodo_aluguel' => 'mensal',
            'valor_total_item' => 1566.30,
        ]);

        // Contrato 3: 18 unidades, total R$ 2.163,68, 12 meses (mensal)
        $contrato3 = Contrato::create([
            'codigo' => 'CTR-0003',
            'data_inicio' => '2025-11-10',
            'data_termino' => '2026-11-10',
            'valor_total' => 2163.68,
            'status' => 'ativo',
            'observacoes' => 'Pagamento: Cartao 3x',
            'locador_id' => $locador->id,
            'locatario_id' => $david->id,
        ]);

        ContratoItem::create([
            'contrato_id' => $contrato3->id,
            'tipo_ativo_id' => $tatame->id,
            'quantidade' => 18,
            'valor_unitario' => 10.02, // 2163.68 / 12 meses / 18 unidades
            'periodo_aluguel' => 'mensal',
            'valor_total_item' => 2163.68,
        ]);
    }
}
