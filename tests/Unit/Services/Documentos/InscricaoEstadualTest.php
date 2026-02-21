<?php

use App\Services\Documentos\InscricaoEstadual;

beforeEach(function () {
    $this->inscricao = new InscricaoEstadual;
});

// Testes de validação

test('valida inscrição estadual com tamanho mínimo (8 dígitos)', function () {
    expect($this->inscricao->validar('12345678'))->toBeTrue();
});

test('valida inscrição estadual com tamanho máximo (14 dígitos)', function () {
    expect($this->inscricao->validar('12345678901234'))->toBeTrue();
});

test('valida inscrição estadual com formatação', function () {
    expect($this->inscricao->validar('123.456.789.012'))->toBeTrue();
});

test('rejeita inscrição estadual muito curta (menos de 8 dígitos)', function () {
    expect($this->inscricao->validar('1234567'))->toBeFalse();
});

test('rejeita inscrição estadual muito longa (mais de 14 dígitos)', function () {
    expect($this->inscricao->validar('123456789012345'))->toBeFalse();
});

// Testes de formatação

test('formatar retorna o valor sem alteração', function () {
    expect($this->inscricao->formatar('123456789012'))->toBe('123456789012');
});

// Testes de limpeza

test('limpar remove caracteres não numéricos', function () {
    expect($this->inscricao->limpar('123.456.789-012'))->toBe('123456789012');
});

// Teste de máscara

test('mascara retorna string vazia (formato variável)', function () {
    expect($this->inscricao->mascara())->toBe('');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->inscricao->mensagemErro())
        ->toBe('Inscrição Estadual inválida. O número deve ter entre 8 e 14 dígitos.');
});
