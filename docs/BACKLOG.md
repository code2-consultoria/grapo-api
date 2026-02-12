# BACKLOG - Grapo

Este documento lista funcionalidades implementadas, em andamento e planejadas para implementação futura.

**Última atualização:** 2025-02-11
**Mantido por:** Equipe de Desenvolvimento

---

## Visao Geral do Projeto

### Status das Sprints

| Sprint | Funcionalidade | Status |
|--------|----------------|--------|
| 01 | Landing Page | Concluída |
| 02 | Contratos | Planejada |

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

---

## PLANEJADO

### Sprint 02 - Contratos
- **Status**: Planejada
- **Prioridade**: Alta
- **Descrição**: Sistema de gestao de contratos de locacao de ativos

**Escopo:**
- Cadastro de pessoas (locador, locatario)
- Cadastro de documentos (CPF, CNPJ, RG, etc) com validacao e formatacao
- Vinculo de users com locadores
- Cadastro de tipos de ativos
- Gestao de lotes
- Criacao e ativacao de contratos
- Alocacao automatica de lotes (FIFO)
- Interfaces CQRS (Query e Command)

**Documentacao:**
- Funcionalidade: `docs/analises/features/contratos.md`
- Modelo de Dados: `docs/analises/modelo-dados.md`

---

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
- **Status**: Backlog
- **Prioridade**: Alta
- **Descrição**: Painel de controle para locadores

**Escopo planejado:**
- Visao geral de contratos ativos
- Alertas de vencimento
- Metricas de ativos/lotes

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

**Ultima revisao:** 2025-02-11 - Sprint 01 concluida, Sprint 02 planejada
**Proxima revisao:** Definicao detalhada da Sprint 02 (Contratos)
