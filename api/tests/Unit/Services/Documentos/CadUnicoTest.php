<?php

use App\Services\Documentos\CadUnico;

beforeEach(function () {
    $this->cadUnico = new CadUnico();
});

// Testes de validação

test('valida NIS/CadÚnico correto', function () {
    // NIS válido gerado com algoritmo correto
    expect($this->cadUnico->validar('12345678900'))->toBeTrue();
});

test('valida NIS com formatação', function () {
    expect($this->cadUnico->validar('123.45678.90-0'))->toBeTrue();
});

test('rejeita NIS com tamanho errado', function () {
    expect($this->cadUnico->validar('1234567890'))->toBeFalse();
    expect($this->cadUnico->validar('123456789012'))->toBeFalse();
});

test('rejeita NIS com todos dígitos iguais', function () {
    expect($this->cadUnico->validar('11111111111'))->toBeFalse();
    expect($this->cadUnico->validar('00000000000'))->toBeFalse();
});

test('rejeita NIS com dígito verificador inválido', function () {
    expect($this->cadUnico->validar('12345678901'))->toBeFalse();
});

// Testes de formatação

test('formata NIS corretamente', function () {
    expect($this->cadUnico->formatar('12345678900'))->toBe('123.45678.90-0');
});

test('formatar retorna original se tamanho inválido', function () {
    expect($this->cadUnico->formatar('123456'))->toBe('123456');
});

// Testes de limpeza

test('limpar remove caracteres não numéricos', function () {
    expect($this->cadUnico->limpar('201.40278.86-7'))->toBe('20140278867');
});

// Teste de máscara

test('mascara retorna formato esperado', function () {
    expect($this->cadUnico->mascara())->toBe('###.#####.##-#');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->cadUnico->mensagemErro())->toBe('NIS/CadÚnico inválido.');
});
