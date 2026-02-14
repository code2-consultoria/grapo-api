# Changelog - Documentacao Grapo

Historico de alteracoes na documentacao do projeto.

Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [Nao publicado]

### Adicionado
- Estrutura de versionamento de documentacao
- MILESTONES.md para rastreamento de marcos
- CHANGELOG.md para historico de alteracoes
- Separacao de modelo de dados (`modelo-dados.md`) das funcionalidades
- Pasta `arquivo/` para versoes arquivadas

### Alterado
- BACKLOG.md atualizado para projeto Grapo
- contratos-diagrama.md dividido em modelo-dados.md e contratos.md

---

## [v1.3.0] - 2025-02-14

**Milestone:** M4 - Monetização (Sprint 05)

### Adicionado
- Integração Stripe Billing (planos trimestral, semestral, anual)
- Stripe Connect Express para locadores receberem pagamentos
- Pagamento recorrente de contratos via Stripe
- Webhooks para eventos Stripe (plataforma e Connect)
- Campo `desconto_comercial` em pagamentos de contrato
- Campo `data_limite_acesso` para controle de acesso por assinatura
- Middleware `VerificaAssinatura` para bloquear acesso quando expirado
- Campo `majoracao_diaria` configurável por locador (padrão 10%)
- Configuração `config/assinatura.php` (trial, dias após pagamento/cancelamento)
- Seção de majoração no perfil do usuário
- Seleção de plano no registro
- Status de assinatura no perfil
- Alerta de expiração no dashboard

### Alterado
- Accessor `valor_diaria_sugerido` em TipoAtivo usa majoracao do locador
- RegisterView.vue com seleção de plano
- IndexView.vue (perfil) com seções de assinatura, Stripe Connect e majoração
- DashboardView.vue com alerta de expiração

### Testes
- 64+ testes passando na Sprint 05
  - PlanoTest: 6 testes
  - StripeTest: 11 testes
  - ConnectTest: 8 testes
  - ContratoPagamentoTest: 11 testes
  - WebhookTest: 7 testes
  - DescontoComercialTest: 8 testes
  - AssinaturaAcessoTest: 13 testes
  - MajoracaoDiariaTest: 8 testes

---

## [v1.1.0] - 2025-02-11

**Milestone:** M1 - MVP Contratos (Refatoracao)

### Alterado
- Modelo de dados refatorado: `tenants` e `locatarios` unificados em `pessoas`
- Campo `tipo` em pessoas: locador, locatario, responsavel_fin, responsavel_adm
- Isolamento por `locador_id` em vez de `tenant_id`
- Terminologia: "tenant" substituido por "locador"

### Adicionado
- Tabela `pessoas` (unificacao de tenant e locatario)
- Tabela `documentos` com tipos: cpf, cnpj, rg, cnh, passaporte, inscricao_municipal, inscricao_estadual, cadunico
- Tabela `vinculo_times` para relacionar users com locadores
- Diagrama de classes para Pessoa e Documento
- Interfaces `ValidaDocumento` e `FormataDocumento`
- `DocumentoFactory` para instanciar classes de documento
- Interfaces CQRS: `Query` e `Command`

### Arquivado
- `docs/analises/arquivo/modelo-dados.m1-v1.md` (versao anterior do modelo)

---

## [v0.1.0] - 2025-02-11

**Milestone:** M0 - Landing Page

### Adicionado
- Documentacao inicial do projeto
- Analise da landing page (`features/landing-page.md`)
- Sprint 01 (`sprints/sprint-01.md`)
- Mockups da landing page
- Estrutura de pastas de documentacao

### Modelo de Dados
- Nenhuma entidade de negocio neste milestone

---

## Proximas Versoes

### [v1.2.0] - Backlog
**Milestone:** M2 - Contratos Completo

- Entidades de aditivos (contrato_aditivos, contrato_aditivo_itens)
- Documentacao de aditivos

---

## Convencoes

- **Adicionado:** Novos documentos ou secoes
- **Alterado:** Mudancas em documentos existentes
- **Removido:** Documentos ou secoes removidas
- **Corrigido:** Correcoes de erros
- **Arquivado:** Versoes movidas para pasta arquivo/
