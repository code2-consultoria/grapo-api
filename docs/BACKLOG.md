# BACKLOG - Grapo

Este documento lista funcionalidades implementadas, em andamento e planejadas para implementação futura.

**Última atualização:** 2025-02-13
**Mantido por:** Equipe de Desenvolvimento

---

## Visao Geral do Projeto

### Status das Sprints

| Sprint | Funcionalidade | Status |
|--------|----------------|--------|
| 01 | Landing Page | Concluída |
| 02 | Contratos | Concluída |
| 03 | Dashboard | Concluída |

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

## BACKLOG (Funcionalidades Futuras)

### Aditivos de Contrato
- **Status**: Despriorizado
- **Prioridade**: Baixa
- **Dependencia**: Sprint 02 (Contratos)
- **Descrição**: Alteracoes formais em contratos apos ativacao

**Tipos de aditivos:**
- Prorrogacao: Extensao do prazo
- Acrescimo: Adicao de itens
- Reducao: Remocao de itens
- Alteracao de valor: Mudanca no valor sem alterar itens

**Documentacao:**
- Proposta: `docs/analises/features/contrato-aditivos-proposta.md`

**Decisoes pendentes:**
- Criterio para liberacao de itens na reducao (LIFO, proporcional ou manual)

---

### Majoracao Percentual da Diaria Configuravel
- **Status**: Backlog
- **Prioridade**: Media
- **Dependencia**: Sprint 02 (Contratos)
- **Descrição**: Permitir que o locador configure o percentual de majoracao para calculo do valor da diaria a partir do valor mensal

**Regras atuais:**
- O valor da diaria e calculado automaticamente: `(valor_mensal * 1.10) / 30`
- Majoracao fixa de 10% sobre o valor mensal

**Escopo:**
- Campo `majoracao_diaria` no model Pessoa (locador)
- Valor padrao: 10% (1.10)
- Interface para locador configurar o percentual
- Atualizar accessor `valor_diaria_sugerido` em TipoAtivo para usar majoracao do locador

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
- **Status**: Backlog
- **Prioridade**: Alta
- **Descrição**: Sistema de login, registro e gestao de usuarios

**Escopo planejado:**
- Login/Logout
- Registro de novos locadores
- Vinculo de users com locadores (vinculo_times)
- Papeis: admin (Grapo), operador (locador)
- Recuperacao de senha

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
- **Status**: Backlog
- **Prioridade**: Media
- **Descrição**: Gestao de planos e cobrancas

**Planos definidos:**
- Trimestral: R$ 75,00
- Semestral: R$ 140,00
- Anual: R$ 250,00

---

## Notas

- **Priorizacao**: Itens marcados como ALTA prioridade devem ser executados antes de MEDIA e BAIXA
- **Dependencias**: Algumas funcionalidades dependem da conclusao de sprints anteriores
- **Documentacao**: Cada sprint deve ter seu arquivo em `docs/sprints/`
- **Analises**: Documentos de analise ficam em `docs/analises/`

---

**Ultima revisao:** 2025-02-13 - Sprint 03 concluida
**Proxima revisao:** Definicao da Sprint 04
