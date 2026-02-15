<?php

use App\Models\Documento;
use App\Models\Pessoa;

beforeEach(function () {
    $this->pessoa = Pessoa::factory()->locatario()->create();
});

test('retorna PF quando pessoa tem CPF', function () {
    Documento::factory()->cpf()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->tipo_pessoa)->toBe('PF');
});

test('retorna PJ quando pessoa tem CNPJ', function () {
    Documento::factory()->cnpj()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->tipo_pessoa)->toBe('PJ');
});

test('retorna PJ quando pessoa tem inscricao municipal', function () {
    Documento::factory()->inscricaoMunicipal()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->tipo_pessoa)->toBe('PJ');
});

test('retorna PJ quando pessoa tem inscricao estadual', function () {
    Documento::factory()->inscricaoEstadual()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->tipo_pessoa)->toBe('PJ');
});

test('retorna null quando pessoa nao tem documentos', function () {
    expect($this->pessoa->tipo_pessoa)->toBeNull();
});

test('prioriza PJ sobre PF quando tem ambos', function () {
    // Cria CPF primeiro
    Documento::factory()->cpf()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    // Depois cria CNPJ
    Documento::factory()->cnpj()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    // Deve priorizar PJ
    expect($this->pessoa->tipo_pessoa)->toBe('PJ');
});

test('retorna PF quando pessoa tem RG', function () {
    Documento::factory()->rg()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->tipo_pessoa)->toBe('PF');
});

test('retorna PF quando pessoa tem CNH', function () {
    Documento::factory()->cnh()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->tipo_pessoa)->toBe('PF');
});

test('helper isPessoaJuridica retorna true para PJ', function () {
    Documento::factory()->cnpj()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->isPessoaJuridica())->toBeTrue();
    expect($this->pessoa->isPessoaFisica())->toBeFalse();
});

test('helper isPessoaFisica retorna true para PF', function () {
    Documento::factory()->cpf()->create([
        'pessoa_id' => $this->pessoa->id,
    ]);

    $this->pessoa->refresh();

    expect($this->pessoa->isPessoaFisica())->toBeTrue();
    expect($this->pessoa->isPessoaJuridica())->toBeFalse();
});
