# Milestones - Grapo

Marcos do projeto que agrupam funcionalidades relacionadas.

**Convencao:** Milestones sao reativos - novos marcos podem ser inseridos antes de outros na fila conforme necessidade do negocio.

---

## Indice de Milestones

| ID | Nome | Status | Sprints | Versao Docs |
|----|------|--------|---------|-------------|
| M0 | Landing Page | Concluido | Sprint 01 | v0.1.0 |
| M1 | MVP Contratos | Planejado | Sprint 02 | v1.0.0 |
| M2 | Contratos Completo | Backlog | - | - |

---

## M0 - Landing Page

**Status:** Concluido
**Versao:** v0.1.0
**Tag Git:** `docs-v0.1.0` (a criar)

### Escopo
- Landing page institucional do Grapo
- Apresentacao do produto e planos

### Funcionalidades
- [x] Landing Page (`docs/analises/features/landing-page.md`)

### Modelo de Dados
- Nenhuma entidade de negocio (apenas frontend)

### Artefatos
- Sprint: `docs/sprints/sprint-01.md`
- Modelo arquivado: `docs/analises/arquivo/modelo-dados.m0.md`

---

## M1 - MVP Contratos

**Status:** Planejado
**Versao:** v1.0.0
**Tag Git:** `docs-v1.0.0` (a criar apos conclusao)

### Escopo
- Sistema basico de gestao de contratos de locacao
- Cadastros fundamentais (locatarios, tipos de ativos, lotes)
- Criacao e ativacao de contratos
- Alocacao automatica de lotes (FIFO)

### Funcionalidades
- [ ] Contratos (`docs/analises/features/contratos.md`)

### Entidades do Modelo
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

### Artefatos
- Sprint: `docs/sprints/sprint-02.md`
- Modelo atual: `docs/analises/modelo-dados.md`

---

## M2 - Contratos Completo

**Status:** Backlog
**Versao:** v1.1.0
**Dependencia:** M1

### Escopo
- Aditivos de contrato (prorrogacao, acrescimo, reducao, alteracao de valor)
- Historico de alteracoes

### Funcionalidades
- [ ] Aditivos (`docs/analises/features/contrato-aditivos-proposta.md`)

### Entidades Adicionais
- contrato_aditivos
- contrato_aditivo_itens

### Decisoes Pendentes
- Criterio para liberacao de itens na reducao (LIFO, proporcional ou manual)

---

## Regras de Versionamento

### Documentacao
- Ao iniciar um milestone, criar tag git: `docs-vX.Y.Z`
- Ao concluir, arquivar modelo de dados: `arquivo/modelo-dados.mN.md`
- Atualizar CHANGELOG.md com alteracoes

### Codigo
- Tags de release seguem semver: `vX.Y.Z`
- Cada milestone pode ter multiplas releases

### Convencoes de Versao
- **Major (X):** Mudancas estruturais no modelo de dados
- **Minor (Y):** Novas entidades ou funcionalidades
- **Patch (Z):** Correcoes e ajustes

---

**Ultima atualizacao:** 2025-02-11
