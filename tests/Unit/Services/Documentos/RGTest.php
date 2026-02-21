<?php

use App\Services\Documentos\RG;

beforeEach(function () {
    $this->rg = new RG;
});

// Testes de validação

test('valida RG com tamanho mínimo (5 dígitos)', function () {
    expect($this->rg->validar('12345'))->toBeTrue();
});

test('valida RG com tamanho máximo (14 dígitos)', function () {
    expect($this->rg->validar('12345678901234'))->toBeTrue();
});

test('valida RG com dígito X', function () {
    expect($this->rg->validar('12345678X'))->toBeTrue();
});

test('valida RG com formatação', function () {
    expect($this->rg->validar('12.345.678-9'))->toBeTrue();
});

test('rejeita RG muito curto (menos de 5 dígitos)', function () {
    expect($this->rg->validar('1234'))->toBeFalse();
});

test('rejeita RG muito longo (mais de 14 dígitos)', function () {
    expect($this->rg->validar('123456789012345'))->toBeFalse();
});

// Testes de formatação

test('formatar retorna o valor sem alteração', function () {
    expect($this->rg->formatar('123456789'))->toBe('123456789');
});

// Testes de limpeza

test('limpar remove caracteres especiais mantendo X', function () {
    expect($this->rg->limpar('12.345.678-X'))->toBe('12345678X');
});

test('limpar remove caracteres especiais', function () {
    expect($this->rg->limpar('12.345.678-9'))->toBe('123456789');
});

// Teste de máscara

test('mascara retorna formato esperado', function () {
    expect($this->rg->mascara())->toBe('##.###.###-#');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->rg->mensagemErro())->toBe('RG inválido. O número deve ter entre 5 e 14 caracteres.');
});
