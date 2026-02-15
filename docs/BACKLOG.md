# BACKLOG - Grapo

Este documento lista funcionalidades implementadas, em andamento e planejadas para implementação futura.

**Última atualização:** 2026-02-14
**Mantido por:** Equipe de Desenvolvimento

---

## Visao Geral do Projeto

### Status das Sprints

| Sprint | Funcionalidade | Status |
|--------|----------------|--------|
| 01 | Landing Page | Concluída |
| 02 | Contratos | Concluída |
| 03 | Dashboard | Concluída |
| 04 | Autenticação | Concluída |
| 05 | Assinaturas e Pagamentos | Concluída |
| 06 | Aditivos de Contrato | Concluída |

---

## CONCLUIDO

### Sprint 01 - Landing Page
- **Status**: Concluída
- **Arquivo**: `docs/sprints/sprint-01.md`
- **Descrição**: Landing page do Grapo com Vue 3 + Tailwind + shadcn-vue

**Entregáveis:**
- [x] Projeto Vue configurado em `front/`
- [x] Componentes: Navbar, Hero, Problems, Features, Pricing, Footer
- [x] Responsividade mobile/tablet/desktop
- [x] Lighthouse scores > 90

### Sprint 02 - Contratos
- **Status**: Concluída
- **Arquivo**: `docs/sprints/sprint-02.md`
- **Descrição**: Sistema de gestao de contratos de locacao de ativos

**Entregáveis:**
- [x] Cadastro de pessoas (locador, locatario) com documentos
- [x] Cadastro de tipos de ativos com gestao de estoque
- [x] Gestao de lotes com custo unitario calculado
- [x] Criacao, edicao e ativacao de contratos
- [x] Alocacao automatica de lotes (FIFO)
- [x] Frontend completo (Index, Create, Edit, Show para todas entidades)
- [x] 73 testes automatizados passando

### Sprint 03 - Dashboard
- **Status**: Concluída
- **Arquivo**: `docs/sprints/sprint-03.md`
- **Descrição**: Dashboard com metricas financeiras e operacionais

**Entregáveis:**
- [x] Endpoint GET /api/dashboard
- [x] Metricas financeiras (receita total, contratos ativos)
- [x] Metricas operacionais (estoque, taxa ocupacao, top ativos)
- [x] Sistema de alertas (vencimento, estoque baixo, esgotados)
- [x] Frontend responsivo com cards e graficos
- [x] 11 testes automatizados passando

### Sprint 04 - Autenticação Completa
- **Status**: Concluída
- **Arquivo**: `docs/sprints/sprint-04.md`
- **Descrição**: Sistema completo de autenticação com registro e recuperação de senha

**Entregáveis:**
- [x] Registro de usuários via API (cria user + locador + vínculo)
- [x] Recuperação de senha (forgot + reset)
- [x] Frontend para recuperação de senha
- [x] Auto-login após registro
- [x] 23 testes automatizados passando

### Sprint 05 - Assinaturas e Pagamentos
- **Status**: Concluída
- **Arquivo**: `docs/sprints/sprint-05.md`
- **Descrição**: Sistema de assinaturas com Stripe, controle de acesso e funcionalidades financeiras

**Entregáveis:**
- [x] Integração Stripe Billing (planos e checkout)
- [x] Stripe Connect Express (locador recebe pagamentos)
- [x] Pagamento recorrente de contratos
- [x] Webhooks Stripe (plataforma e Connect)
- [x] Desconto comercial em pagamentos
- [x] Controle de acesso por assinatura (trial, data limite)
- [x] Majoração da diária configurável por locador
- [x] 64+ testes automatizados passando

### Sprint 06 - Aditivos de Contrato
- **Status**: Concluída
- **Arquivo**: `docs/sprints/sprint-06.md`
- **Descrição**: Sistema de aditivos para contratos ativos

**Entregáveis:**
- [x] Tipos de aditivos: prorrogação, acréscimo, redução, alteração de valor
- [x] Fluxo completo: rascunho → ativo → cancelado
- [x] Alocação FIFO para acréscimos
- [x] Liberação LIFO para reduções
- [x] Integração Stripe (cobrança proporcional, atualização de subscription)
- [x] Cancelamento com reversão completa
- [x] Frontend com componente ContratoAditivos
- [x] 38 testes automatizados passando

## BACKLOG (Funcionalidades Futuras)

### Importacao de Historico de Pagamentos/Faturas
- **Status**: Backlog
- **Prioridade**: Media
- **Dependencia**: Sprint 05 (Pagamentos)
- **Descrição**: Permitir importacao em massa de historico de pagamentos/faturas de contratos existentes

**Requisitos:**
- Upload de arquivo CSV/Excel com dados de pagamentos
- Mapeamento de colunas (contrato, valor, data vencimento, data pagamento, etc.)
- Validacao de dados antes da importacao
- Preview dos dados a serem importados
- Importacao em lote com tratamento de erros
- Relatorio de importacao (sucesso/erros)

**Campos para importacao:**
- Codigo do contrato (obrigatorio)
- Valor (obrigatorio)
- Desconto comercial (opcional)
- Data de vencimento (obrigatorio)
- Data de pagamento (opcional - se preenchido, marca como pago)
- Origem (manual, pix)
- Observacoes (opcional)

---

### Majoracao Percentual da Diaria Configuravel
- **Status**: Concluido (Sprint 05)
- **Prioridade**: Media
- **Dependencia**: Sprint 02 (Contratos)
- **Descrição**: Permitir que o locador configure o percentual de majoracao para calculo do valor da diaria a partir do valor mensal

**Implementado:**
- Campo `majoracao_diaria` (decimal 5,2) no model Pessoa (locador)
- Valor padrao: 10%
- Seção no perfil do usuário para configuração
- Accessor `valor_diaria_sugerido` em TipoAtivo usa majoracao do locador
- Endpoints: GET/PUT /perfil/majoracao
- 8 testes automatizados

---

### Accessor tipo_pessoa no Model Pessoa
- **Status**: Backlog
- **Prioridade**: Media
- **Dependencia**: Sprint 02 (Contratos)
- **Descrição**: Criar accessor que descobre se pessoa e PF ou PJ baseado nos documentos

**Regras:**
- **PJ**: Se tem CNPJ, Inscricao Municipal ou Inscricao Estadual
- **PF**: Se tem CPF, RG, CadUnico, Passaporte ou CNH

---

### Autenticacao e Usuarios
- **Status**: Concluido (Sprint 04)
- **Prioridade**: Alta
- **Descrição**: Sistema de login, registro e gestao de usuarios

**Implementado:**
- Login/Logout via API Sanctum
- Registro de novos locadores (cria user + locador + vinculo)
- Vinculo de users com locadores (vinculo_times)
- Papeis: admin (Grapo), cliente (locador)
- Recuperacao de senha (forgot + reset)

---

### Dashboard
- **Status**: Concluido (Sprint 03)
- **Prioridade**: Alta
- **Descrição**: Painel de controle para locadores

**Implementado:**
- Visao geral de contratos ativos
- Alertas de vencimento e estoque
- Metricas financeiras e operacionais
- Top 5 ativos mais alugados
- Taxa de ocupacao do estoque

---

### Assinaturas e Pagamentos
- **Status**: Concluido (Sprint 05)
- **Prioridade**: Media
- **Descrição**: Gestao de planos e cobrancas

**Planos definidos:**
- Trimestral: R$ 75,00
- Semestral: R$ 140,00
- Anual: R$ 250,00

**Implementado:**
- Integração Stripe Billing completa
- Stripe Connect Express para locadores
- Controle de acesso por data limite
- Trial configurável (padrão 7 dias)
- Desconto comercial em pagamentos
- Webhooks para eventos Stripe

---

## Notas

- **Priorizacao**: Itens marcados como ALTA prioridade devem ser executados antes de MEDIA e BAIXA
- **Dependencias**: Algumas funcionalidades dependem da conclusao de sprints anteriores
- **Documentacao**: Cada sprint deve ter seu arquivo em `docs/sprints/`
- **Analises**: Documentos de analise ficam em `docs/analises/`

---

**Ultima revisao:** 2026-02-14 - Sprint 06 concluida
**Proxima revisao:** Definicao da Sprint 07
