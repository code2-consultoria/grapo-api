---
name: test
description: Executa os testes automatizados do projeto Laravel. Use quando precisar rodar testes, verificar se o código está funcionando, ou antes de fazer commit/deploy.
argument-hint: "[filter] [--coverage]"
disable-model-invocation: true
allowed-tools: Bash(php artisan test*), Bash(docker exec*)
---

# Skill: Executar Testes

Executa os testes automatizados do projeto usando Pest/PHPUnit.

## Uso

- `/test` - Roda todos os testes
- `/test NomeDoTeste` - Filtra testes pelo nome
- `/test --coverage` - Roda com relatório de cobertura
- `/test Feature/Auth` - Roda testes de um diretório específico

## Instruções

1. **Identificar ambiente**: Verifique se existe um container Docker rodando para o projeto
2. **Executar testes**:
   - Se houver container: `docker exec -it <container> php artisan test $ARGUMENTS`
   - Se local: `cd api && php artisan test $ARGUMENTS`
3. **Analisar resultados**:
   - Se todos passarem, informe o total de testes e tempo
   - Se houver falhas, liste os testes que falharam e sugira correções
4. **Relatório**: Apresente um resumo conciso dos resultados

## Opções Comuns

| Opção | Descrição |
|-------|-----------|
| `--filter="nome"` | Filtra testes pelo nome |
| `--coverage` | Gera relatório de cobertura |
| `--stop-on-failure` | Para na primeira falha |
| `--parallel` | Executa em paralelo |

## Exemplo de Output Esperado

```
✓ 98 testes passando (273 assertions)
⏱ Duração: 2.10s

Tudo funcionando! Pronto para deploy.
```

Ou em caso de falha:

```
✗ 2 testes falhando

Falhas:
1. Tests\Feature\UserTest > cria usuário
   - Erro: Expected status 201, got 422
   - Arquivo: tests/Feature/UserTest.php:45

Sugestão: Verificar validação de campos obrigatórios
```
