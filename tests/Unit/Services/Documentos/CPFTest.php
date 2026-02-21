<?php

use App\Services\Documentos\CPF;

beforeEach(function () {
    $this->cpf = new CPF;
});

// Testes de validação

test('valida CPF correto', function () {
    expect($this->cpf->validar('52998224725'))->toBeTrue();
});

test('valida CPF com formatação', function () {
    expect($this->cpf->validar('529.982.247-25'))->toBeTrue();
});

test('valida outro CPF correto', function () {
    expect($this->cpf->validar('11144477735'))->toBeTrue();
});

test('rejeita CPF com tamanho errado', function () {
    expect($this->cpf->validar('1234567890'))->toBeFalse();
    expect($this->cpf->validar('123456789012'))->toBeFalse();
});

test('rejeita CPF com todos dígitos iguais', function () {
    expect($this->cpf->validar('11111111111'))->toBeFalse();
    expect($this->cpf->validar('00000000000'))->toBeFalse();
    expect($this->cpf->validar('22222222222'))->toBeFalse();
});

test('rejeita CPF com primeiro dígito verificador inválido', function () {
    expect($this->cpf->validar('52998224715'))->toBeFalse();
});

test('rejeita CPF com segundo dígito verificador inválido', function () {
    expect($this->cpf->validar('52998224726'))->toBeFalse();
});

// Testes de formatação

test('formata CPF corretamente', function () {
    expect($this->cpf->formatar('52998224725'))->toBe('529.982.247-25');
});

test('formatar retorna original se tamanho inválido', function () {
    expect($this->cpf->formatar('123456'))->toBe('123456');
});

// Testes de limpeza

test('limpar remove pontos e traços', function () {
    expect($this->cpf->limpar('529.982.247-25'))->toBe('52998224725');
});

test('limpar remove qualquer caractere não numérico', function () {
    expect($this->cpf->limpar('529 982 247 25'))->toBe('52998224725');
});

// Teste de máscara

test('mascara retorna formato esperado', function () {
    expect($this->cpf->mascara())->toBe('###.###.###-##');
});

// Teste de mensagem de erro

test('mensagemErro retorna mensagem correta', function () {
    expect($this->cpf->mensagemErro())->toBe('CPF inválido.');
});
