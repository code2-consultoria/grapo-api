<?php

use App\Services\Documentos\InscricaoMunicipal;

beforeEach(function () {
    $this->inscricao = new InscricaoMunicipal();
});

// Testes de validação

test('valida inscrição municipal com tamanho mínimo (5 dígitos)', function () {
    expect($this->inscricao->validar('12345'))->toBeTrue();
});

test('valida inscrição municipal com tamanho máximo (20 dígitos)', function () {
    expect($this->inscricao->validar('12345678901234567890'))->toBeTrue();
});

test('valida inscrição municipal com formatação', function () {
    expect($this->inscricao->validar('123.456.789'))->toBeTrue();
});

test('rejeita inscrição municipal muito curta (menos de 5 dígitos)', function () {
    expect($this->inscricao->validar('1234'))->toBeFalse();
});

test('rejeita inscrição municipal muito longa (mais de 20 dígitos)', function () {
    expect($this->inscricao->validar('123456789012345678901'))->toBeFalse();
});

// Testes de formatação

test('formatar retorna o valor sem alteração', function () {
    expect($this->inscricao->formatar('123456789'))->toBe('123456789');
});

// Testes de limpeza

test('limpar remove caracteres não numéricos', function () {
    expect($this->inscricao->limpar('123.456.789-00'))->toBe('12345678900');
});

// Teste de máscara

test('mascara retorna string vazia (formato variável)', function () {
    expect($this->inscricao->mascara())->toBe('');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->inscricao->mensagemErro())
        ->toBe('Inscrição Municipal inválida. O número deve ter entre 5 e 20 dígitos.');
});
