<?php

use App\Services\Documentos\CNPJ;

beforeEach(function () {
    $this->cnpj = new CNPJ;
});

// Testes de validação

test('valida CNPJ correto', function () {
    expect($this->cnpj->validar('11222333000181'))->toBeTrue();
});

test('valida CNPJ com formatação', function () {
    expect($this->cnpj->validar('11.222.333/0001-81'))->toBeTrue();
});

test('valida outro CNPJ correto', function () {
    expect($this->cnpj->validar('11444777000161'))->toBeTrue();
});

test('rejeita CNPJ com tamanho errado', function () {
    expect($this->cnpj->validar('1122233300018'))->toBeFalse();
    expect($this->cnpj->validar('112223330001811'))->toBeFalse();
});

test('rejeita CNPJ com todos dígitos iguais', function () {
    expect($this->cnpj->validar('11111111111111'))->toBeFalse();
    expect($this->cnpj->validar('00000000000000'))->toBeFalse();
    expect($this->cnpj->validar('22222222222222'))->toBeFalse();
});

test('rejeita CNPJ com primeiro dígito verificador inválido', function () {
    expect($this->cnpj->validar('11222333000171'))->toBeFalse();
});

test('rejeita CNPJ com segundo dígito verificador inválido', function () {
    expect($this->cnpj->validar('11222333000182'))->toBeFalse();
});

// Testes de formatação

test('formata CNPJ corretamente', function () {
    expect($this->cnpj->formatar('11222333000181'))->toBe('11.222.333/0001-81');
});

test('formatar retorna original se tamanho inválido', function () {
    expect($this->cnpj->formatar('123456'))->toBe('123456');
});

// Testes de limpeza

test('limpar remove pontos, barras e traços', function () {
    expect($this->cnpj->limpar('11.222.333/0001-81'))->toBe('11222333000181');
});

test('limpar remove qualquer caractere não numérico', function () {
    expect($this->cnpj->limpar('11 222 333 0001 81'))->toBe('11222333000181');
});

// Teste de máscara

test('mascara retorna formato esperado', function () {
    expect($this->cnpj->mascara())->toBe('##.###.###/####-##');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->cnpj->mensagemErro())->toBe('CNPJ inválido.');
});
