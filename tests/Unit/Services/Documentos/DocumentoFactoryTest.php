<?php

use App\Enums\TipoDocumento;
use App\Services\Documentos\DocumentoFactory;

// Testes de detecção automática de tipo

test('detecta CPF pelo tamanho (11 dígitos)', function () {
    $tipo = DocumentoFactory::detectarTipo('52998224725');
    expect($tipo)->toBe(TipoDocumento::Cpf);
});

test('detecta CNPJ pelo tamanho (14 dígitos)', function () {
    $tipo = DocumentoFactory::detectarTipo('11222333000181');
    expect($tipo)->toBe(TipoDocumento::Cnpj);
});

test('detecta CPF com formatação', function () {
    $tipo = DocumentoFactory::detectarTipo('529.982.247-25');
    expect($tipo)->toBe(TipoDocumento::Cpf);
});

test('detecta CNPJ com formatação', function () {
    $tipo = DocumentoFactory::detectarTipo('11.222.333/0001-81');
    expect($tipo)->toBe(TipoDocumento::Cnpj);
});

test('lança exceção para tamanho inválido', function () {
    DocumentoFactory::detectarTipo('123456789');
})->throws(InvalidArgumentException::class, 'Documento deve ter 11 (CPF) ou 14 (CNPJ) dígitos.');

test('lança exceção para string vazia', function () {
    DocumentoFactory::detectarTipo('');
})->throws(InvalidArgumentException::class);

// Testes de validação com detecção automática

test('valida CPF automaticamente', function () {
    $resultado = DocumentoFactory::validarAuto('52998224725');
    expect($resultado)->toBeTrue();
});

test('valida CNPJ automaticamente', function () {
    $resultado = DocumentoFactory::validarAuto('11222333000181');
    expect($resultado)->toBeTrue();
});

test('rejeita CPF inválido automaticamente', function () {
    $resultado = DocumentoFactory::validarAuto('12345678901');
    expect($resultado)->toBeFalse();
});

test('rejeita CNPJ inválido automaticamente', function () {
    $resultado = DocumentoFactory::validarAuto('12345678000199');
    expect($resultado)->toBeFalse();
});

// Testes de validação de CPF existentes

test('valida CPF correto', function () {
    $resultado = DocumentoFactory::validar(TipoDocumento::Cpf, '52998224725');
    expect($resultado)->toBeTrue();
});

test('rejeita CPF com todos dígitos iguais', function () {
    $resultado = DocumentoFactory::validar(TipoDocumento::Cpf, '11111111111');
    expect($resultado)->toBeFalse();
});

test('rejeita CPF com tamanho errado', function () {
    $resultado = DocumentoFactory::validar(TipoDocumento::Cpf, '1234567890');
    expect($resultado)->toBeFalse();
});

// Testes de validação de CNPJ existentes

test('valida CNPJ correto', function () {
    $resultado = DocumentoFactory::validar(TipoDocumento::Cnpj, '11222333000181');
    expect($resultado)->toBeTrue();
});

test('rejeita CNPJ com todos dígitos iguais', function () {
    $resultado = DocumentoFactory::validar(TipoDocumento::Cnpj, '11111111111111');
    expect($resultado)->toBeFalse();
});

test('rejeita CNPJ com tamanho errado', function () {
    $resultado = DocumentoFactory::validar(TipoDocumento::Cnpj, '1122233300018');
    expect($resultado)->toBeFalse();
});

// Testes de formatação

test('formata CPF corretamente', function () {
    $formatado = DocumentoFactory::formatar(TipoDocumento::Cpf, '52998224725');
    expect($formatado)->toBe('529.982.247-25');
});

test('formata CNPJ corretamente', function () {
    $formatado = DocumentoFactory::formatar(TipoDocumento::Cnpj, '11222333000181');
    expect($formatado)->toBe('11.222.333/0001-81');
});

// Testes de limpeza

test('limpa formatação de CPF', function () {
    $limpo = DocumentoFactory::limpar(TipoDocumento::Cpf, '529.982.247-25');
    expect($limpo)->toBe('52998224725');
});

test('limpa formatação de CNPJ', function () {
    $limpo = DocumentoFactory::limpar(TipoDocumento::Cnpj, '11.222.333/0001-81');
    expect($limpo)->toBe('11222333000181');
});
