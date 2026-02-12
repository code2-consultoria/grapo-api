# Modelo de Dados - Grapo (Arquivado)

> **Versao:** 1.0.0 (M1 - MVP Contratos - Versao Inicial)
> **Data de arquivamento:** 2025-02-11
> **Motivo:** Refatoracao do modelo - unificacao de Tenant/Locatario em Pessoa

---

Este arquivo contem a versao inicial do modelo M1 antes da refatoracao.

## Entidades (versao arquivada)

- usuarios
- tenants
- planos
- assinaturas
- locatarios
- tipos_ativos
- lotes
- contratos
- contrato_itens
- alocacoes_lotes

## Mudancas aplicadas na nova versao

1. `tenants` e `locatarios` unificados em `pessoas`
2. Novo campo `tipo` em pessoas (locador, locatario, responsavel_fin, responsavel_adm)
3. Nova tabela `documentos` para CPF, CNPJ, RG, etc
4. Nova tabela `vinculo_times` para relacionar users com locadores
5. `contratos` agora tem `locador_id` e `locatario_id` (ambos referenciando `pessoas`)
6. Isolamento por `locador_id` em vez de `tenant_id`

Ver modelo atual: `docs/analises/modelo-dados.md`
