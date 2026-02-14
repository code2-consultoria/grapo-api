<?php

use App\Services\Documentos\CNH;

beforeEach(function () {
    $this->cnh = new CNH();
});

// Testes de validação

test('valida CNH correta', function () {
    // CNH válida gerada com algoritmo correto
    expect($this->cnh->validar('12345678900'))->toBeTrue();
});

test('valida CNH com formatação', function () {
    expect($this->cnh->validar('123 45678 900'))->toBeTrue();
});

test('rejeita CNH com tamanho errado', function () {
    expect($this->cnh->validar('1234567890'))->toBeFalse();
    expect($this->cnh->validar('123456789012'))->toBeFalse();
});

test('rejeita CNH com todos dígitos iguais', function () {
    expect($this->cnh->validar('11111111111'))->toBeFalse();
    expect($this->cnh->validar('00000000000'))->toBeFalse();
});

test('rejeita CNH com dígito verificador inválido', function () {
    expect($this->cnh->validar('12345678901'))->toBeFalse();
});

// Testes de formatação

test('formata CNH corretamente', function () {
    expect($this->cnh->formatar('12345678900'))->toBe('123 45678 900');
});

test('formatar retorna original se tamanho inválido', function () {
    expect($this->cnh->formatar('123456'))->toBe('123456');
});

// Testes de limpeza

test('limpar remove caracteres não numéricos', function () {
    expect($this->cnh->limpar('049 41882 270'))->toBe('04941882270');
});

// Teste de máscara

test('mascara retorna formato esperado', function () {
    expect($this->cnh->mascara())->toBe('### ##### ###');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->cnh->mensagemErro())->toBe('CNH inválida.');
});
