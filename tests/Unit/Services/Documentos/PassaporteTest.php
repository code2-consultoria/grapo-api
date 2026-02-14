<?php

use App\Services\Documentos\Passaporte;

beforeEach(function () {
    $this->passaporte = new Passaporte();
});

// Testes de validação - formato brasileiro

test('valida passaporte brasileiro (2 letras + 6 dígitos)', function () {
    expect($this->passaporte->validar('AB123456'))->toBeTrue();
});

test('valida passaporte brasileiro em minúsculo', function () {
    expect($this->passaporte->validar('ab123456'))->toBeTrue();
});

test('valida passaporte brasileiro com letras FN', function () {
    expect($this->passaporte->validar('FN123456'))->toBeTrue();
});

// Testes de validação - formato internacional

test('valida passaporte internacional 6 caracteres', function () {
    expect($this->passaporte->validar('A12345'))->toBeTrue();
});

test('valida passaporte internacional 12 caracteres', function () {
    expect($this->passaporte->validar('AB1234567890'))->toBeTrue();
});

test('valida passaporte apenas numérico', function () {
    expect($this->passaporte->validar('123456789'))->toBeTrue();
});

// Testes de rejeição

test('rejeita passaporte muito curto (menos de 6 caracteres)', function () {
    expect($this->passaporte->validar('A1234'))->toBeFalse();
});

test('rejeita passaporte muito longo (mais de 12 caracteres)', function () {
    expect($this->passaporte->validar('AB12345678901'))->toBeFalse();
});

test('rejeita passaporte com caracteres especiais', function () {
    expect($this->passaporte->validar('AB-123456'))->toBeFalse();
});

// Testes de formatação

test('formata passaporte para maiúsculo', function () {
    expect($this->passaporte->formatar('ab123456'))->toBe('AB123456');
});

test('formata passaporte removendo espaços', function () {
    expect($this->passaporte->formatar(' AB123456 '))->toBe('AB123456');
});

// Testes de limpeza

test('limpar remove caracteres especiais', function () {
    expect($this->passaporte->limpar('AB-123.456'))->toBe('AB123456');
});

// Teste de máscara

test('mascara retorna formato brasileiro', function () {
    expect($this->passaporte->mascara())->toBe('AA######');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->passaporte->mensagemErro())
        ->toBe('Passaporte inválido. Use o formato brasileiro (XX000000) ou formato internacional.');
});
