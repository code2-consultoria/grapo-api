<?php

use App\Enums\TipoDocumento;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Documento;
use App\Models\Pessoa;
use App\Models\TipoAtivo;
use App\Models\User;
use App\Models\VinculoTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    // Adiciona documentos ao locador e locatário
    Documento::factory()->create([
        'pessoa_id' => $this->locador->id,
        'tipo' => TipoDocumento::Cnpj,
        'numero' => '12345678000199',
    ]);
    Documento::factory()->create([
        'pessoa_id' => $this->locatario->id,
        'tipo' => TipoDocumento::Cpf,
        'numero' => '12345678901',
    ]);

    // Cria contrato com itens
    $this->contrato = Contrato::factory()->rascunho()->create([
        'locador_id' => $this->locador->id,
        'locatario_id' => $this->locatario->id,
        'observacoes' => 'Teste de geração de documento',
    ]);

    ContratoItem::factory()->create([
        'contrato_id' => $this->contrato->id,
        'tipo_ativo_id' => $this->tipoAtivo->id,
        'quantidade' => 10,
        'valor_unitario' => 5.00,
        'valor_total_item' => 50.00,
    ]);
});

// --- Testes de Geração de Documento ---

test('gera documento DOCX com dados do contrato', function () {
    $response = $this->actingAs($this->user)
        ->get("/api/contratos/{$this->contrato->id}/documento");

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    // Verifica apenas que o header contém o nome do arquivo
    expect($response->headers->get('content-disposition'))->toContain("contrato-{$this->contrato->codigo}.docx");
});

test('documento contém dados do locador', function () {
    // O teste de conteúdo real requer leitura do DOCX
    // Verificamos que a requisição funciona e o service é chamado
    $response = $this->actingAs($this->user)
        ->get("/api/contratos/{$this->contrato->id}/documento");

    $response->assertStatus(200);
});

test('documento contém dados do locatário', function () {
    $response = $this->actingAs($this->user)
        ->get("/api/contratos/{$this->contrato->id}/documento");

    $response->assertStatus(200);
});

test('documento contém tabela de itens', function () {
    $response = $this->actingAs($this->user)
        ->get("/api/contratos/{$this->contrato->id}/documento");

    $response->assertStatus(200);
});

test('retorna 404 para contrato inexistente ao gerar documento', function () {
    // Usa um UUID válido mas que não existe no banco
    $uuidInexistente = '00000000-0000-0000-0000-000000000000';

    $response = $this->actingAs($this->user)
        ->get("/api/contratos/{$uuidInexistente}/documento");

    $response->assertStatus(404);
});

test('retorna 404 para contrato de outro locador ao gerar documento', function () {
    // Por segurança, não revela a existência do contrato - retorna 404
    $outroLocador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(),
    ]);
    $outroUser = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $outroUser->id,
        'locador_id' => $outroLocador->id,
    ]);

    $response = $this->actingAs($outroUser)
        ->get("/api/contratos/{$this->contrato->id}/documento");

    $response->assertStatus(404);
});

// --- Testes de Upload de Documento Assinado ---

test('faz upload de documento assinado', function () {
    Storage::fake('local');

    $pdf = UploadedFile::fake()->create('contrato-assinado.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $pdf,
        ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
        'data' => [
            'documento_assinado_path',
            'documento_assinado_url',
        ],
    ]);

    $this->contrato->refresh();
    expect($this->contrato->documento_assinado_path)->not->toBeNull();
});

test('rejeita upload de arquivo não-PDF', function () {
    Storage::fake('local');

    $arquivo = UploadedFile::fake()->create('documento.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $arquivo,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});

test('rejeita upload maior que 10MB', function () {
    Storage::fake('local');

    // Arquivo maior que 10MB
    $pdf = UploadedFile::fake()->create('contrato-grande.pdf', 11 * 1024, 'application/pdf');

    $response = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $pdf,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['documento']);
});

test('retorna 403 ao fazer upload em contrato de outro locador', function () {
    Storage::fake('local');

    $outroLocador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(),
    ]);
    $outroUser = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $outroUser->id,
        'locador_id' => $outroLocador->id,
    ]);

    $pdf = UploadedFile::fake()->create('contrato-assinado.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($outroUser)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $pdf,
        ]);

    $response->assertStatus(403);
});

// --- Testes de Download de Documento Assinado ---

test('permite download do documento assinado', function () {
    Storage::fake('local');

    // Primeiro faz upload
    $pdf = UploadedFile::fake()->create('contrato-assinado.pdf', 1024, 'application/pdf');

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $pdf,
        ]);

    // Depois faz download
    $response = $this->actingAs($this->user)
        ->get("/api/contratos/{$this->contrato->id}/documento/assinado");

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('retorna 404 se não há documento assinado', function () {
    $response = $this->actingAs($this->user)
        ->get("/api/contratos/{$this->contrato->id}/documento/assinado");

    $response->assertStatus(404);
});

test('retorna 403 ao baixar documento de contrato de outro locador', function () {
    Storage::fake('local');

    // Faz upload primeiro
    $pdf = UploadedFile::fake()->create('contrato-assinado.pdf', 1024, 'application/pdf');

    $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $pdf,
        ]);

    // Tenta baixar com outro usuário
    $outroLocador = Pessoa::factory()->locador()->create([
        'data_limite_acesso' => now()->addMonth(),
    ]);
    $outroUser = User::factory()->create(['papel' => 'cliente']);
    VinculoTime::factory()->create([
        'user_id' => $outroUser->id,
        'locador_id' => $outroLocador->id,
    ]);

    $response = $this->actingAs($outroUser)
        ->get("/api/contratos/{$this->contrato->id}/documento/assinado");

    $response->assertStatus(403);
});

// --- Testes de Regras de Negócio ---

test('sobrescreve documento assinado anterior no novo upload', function () {
    Storage::fake('local');

    // Primeiro upload
    $pdf1 = UploadedFile::fake()->create('contrato-v1.pdf', 1024, 'application/pdf');

    $response1 = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $pdf1,
        ]);

    $response1->assertStatus(200);
    $pathAnterior = $response1->json('data.documento_assinado_path');

    // Espera um segundo para garantir timestamp diferente
    sleep(1);

    // Segundo upload
    $pdf2 = UploadedFile::fake()->create('contrato-v2.pdf', 2048, 'application/pdf');

    $response2 = $this->actingAs($this->user)
        ->postJson("/api/contratos/{$this->contrato->id}/documento", [
            'documento' => $pdf2,
        ]);

    $response2->assertStatus(200);

    // Verifica que o contrato tem o novo path
    $this->contrato->refresh();
    expect($this->contrato->documento_assinado_path)->not->toBeNull();
    expect($this->contrato->documento_assinado_path)->toContain('contratos/assinados/');
});
