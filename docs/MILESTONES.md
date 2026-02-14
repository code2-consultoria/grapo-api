# Milestones - Grapo

Marcos do projeto que agrupam funcionalidades relacionadas.

**Convencao:** Milestones sao reativos - novos marcos podem ser inseridos antes de outros na fila conforme necessidade do negocio.

---

## Indice de Milestones

| ID | Nome | Status | Sprints | Versao Docs |
|----|------|--------|---------|-------------|
| M0 | Landing Page | Concluido | Sprint 01 | v0.1.0 |
| M1 | MVP Contratos | Concluido | Sprint 02 | v1.1.0 |
| M2 | Contratos Completo | Backlog | - | - |
| M3 | Dashboard e Autenticação | Concluido | Sprint 03, 04 | v1.1.0 |
| M4 | Monetização | Concluido | Sprint 05 | v1.2.0 |

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

**Status:** Concluido
**Versao:** v1.1.0
**Tag Git:** `docs-v1.1.0`

### Escopo
- Sistema basico de gestao de contratos de locacao
- Cadastros fundamentais (locatarios, tipos de ativos, lotes)
- Criacao e ativacao de contratos
- Alocacao automatica de lotes (FIFO)

### Funcionalidades
- [x] Contratos (`docs/analises/features/contratos.md`)

### Entidades do Modelo
- users
- vinculo_times
- pessoas
- documentos
- planos
- assinaturas
- tipos_ativos
- lotes
- contratos
- contrato_itens
- alocacoes_lotes

### Artefatos
- Sprint: `docs/sprints/sprint-02.md`
- Modelo: `docs/analises/modelo-dados.md`

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

## M3 - Dashboard e Autenticação

**Status:** Concluido
**Versao:** v1.1.0
**Dependencia:** M1

### Escopo
- Dashboard com metricas financeiras e operacionais
- Sistema completo de autenticacao (registro, recuperacao de senha)

### Funcionalidades
- [x] Dashboard (`docs/sprints/sprint-03.md`)
- [x] Autenticação completa (`docs/sprints/sprint-04.md`)

### Artefatos
- Sprint 03: `docs/sprints/sprint-03.md`
- Sprint 04: `docs/sprints/sprint-04.md`

---

## M4 - Monetização

**Status:** Concluido
**Versao:** v1.2.0
**Dependencia:** M3

### Escopo
- Integracao Stripe Billing (assinaturas da plataforma)
- Stripe Connect Express (locadores recebem pagamentos)
- Pagamento recorrente de contratos
- Controle de acesso por assinatura
- Majoracao da diaria configuravel
- Desconto comercial em pagamentos

### Funcionalidades
- [x] Stripe Billing (planos, checkout, cancelamento)
- [x] Stripe Connect (onboarding, status, dashboard)
- [x] Pagamento de contratos via Stripe
- [x] Webhooks Stripe (plataforma e Connect)
- [x] Controle de acesso (trial, data limite, middleware)
- [x] Majoracao da diaria configuravel
- [x] Desconto comercial em pagamentos

### Entidades Adicionais
- pagamentos
- stripe_subscriptions
- stripe_subscription_items

### Campos Adicionais
- pessoas: data_limite_acesso, majoracao_diaria, stripe_*, stripe_connect_config
- contratos: tipo_cobranca, dia_vencimento, stripe_subscription_id, stripe_customer_id
- pagamentos: desconto_comercial

### Artefatos
- Sprint: `docs/sprints/sprint-05.md`
- Modelo: `docs/analises/modelo-dados.md` (v1.2.0)

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

**Ultima atualizacao:** 2025-02-14
