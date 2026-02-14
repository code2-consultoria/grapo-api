# Sprint 02 - MVP Contratos

> **Periodo:** 2025-02-11 a 2025-02-13
> **Status:** Concluida
> **Milestone:** M1 - MVP Contratos
> **Objetivo:** Implementar sistema basico de gestao de contratos de locacao

---

## Backlog da Sprint

### Features

| ID | Feature | Prioridade | Status |
|----|---------|------------|--------|
| F01 | Contratos | Alta | Concluido |
| F02 | Frontend Vue.js | Alta | Concluido |

---

## Tarefas

### 0. Frontend Vue.js (Estrutura)

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T00.1 | Tipos TypeScript | models, api, forms | Concluido |
| T00.2 | Cliente HTTP | lib/api.ts com fetch | Concluido |
| T00.3 | Stores Pinia | auth, notification | Concluido |
| T00.4 | Composables | useAuth, useApi, useForm, useNotification | Concluido |
| T00.5 | Componentes UI | input, select, dialog, table, toast, etc | Concluido |
| T00.6 | Componentes Forms | FormField, FormGroup, FormActions | Concluido |
| T00.7 | Componentes App | Sidebar, Header, UserMenu, StatCard | Concluido |
| T00.8 | Layouts | AuthLayout, AppLayout | Concluido |
| T00.9 | Router + Guards | Rotas protegidas, nested routes | Concluido |
| T00.10 | Views Auth | Login, Register | Concluido |
| T00.11 | Views App | Dashboard, CRUD stubs | Concluido |
| T00.12 | Links Landing Page | Conectar Navbar/Hero a Login/Register | Concluido |

> Documentacao: [docs/FRONTEND.md](../FRONTEND.md)

### 1. Setup do Backend

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T01 | Criar migrations | Todas as tabelas do modelo de dados | Concluido |
| T02 | Criar models | Eloquent models com relacionamentos | Concluido |
| T03 | Criar factories | Factories para testes | Concluido |
| T04 | Criar seeders | Dados iniciais (planos, tenant demo) | Concluido |

### 2. Cadastro de Locatarios

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T05 | Testes - Locatarios | Testes de CRUD e validacoes (18 testes) | Concluido |
| T06 | Action - Criar | `Locatario/Criar` | Concluido |
| T07 | Action - Atualizar | `Locatario/Atualizar` | Concluido |
| T08 | Action - Excluir | `Locatario/Excluir` | Concluido |
| T09 | Controllers | `Locatario/Index`, `Store`, `Show`, `Update`, `Destroy` | Concluido |

### 3. Cadastro de Tipos de Ativos

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T10 | Testes - Tipos Ativos | Testes de CRUD e validacoes (7 testes) | Concluido |
| T11 | Action - Criar | `TipoAtivo/Criar` | Concluido |
| T12 | Action - Atualizar | `TipoAtivo/Atualizar` | Concluido |
| T13 | Action - Excluir | `TipoAtivo/Excluir` | Concluido |
| T14 | Controllers | `TipoAtivo/Index`, `Store`, `Show`, `Update`, `Destroy` | Concluido |

### 4. Gestao de Lotes

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T15 | Testes - Lotes | Testes de CRUD, disponibilidade e validacoes (10 testes) | Concluido |
| T16 | Action - Criar | `Lote/Criar` | Concluido |
| T17 | Action - Atualizar | `Lote/Atualizar` | Concluido |
| T18 | Action - Excluir | `Lote/Excluir` (validar se nao alocado) | Concluido |
| T19 | Controllers | `Lote/Index`, `Store`, `Show`, `Update`, `Destroy` | Concluido |

### 5. Contratos

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T20 | Testes - Contratos | Testes de criacao, ativacao, cancelamento | Concluido |
| T21 | Action - Criar | `Contrato/Criar` (status rascunho) | Concluido |
| T22 | Action - Ativar | `Contrato/Ativar` (aloca lotes FIFO) | Concluido |
| T23 | Action - Cancelar | `Contrato/Cancelar` (libera lotes) | Concluido |
| T24 | Action - Finalizar | `Contrato/Finalizar` (libera lotes) | Concluido |
| T25 | Controllers | `Contrato/Index`, `Store`, `Show`, `Ativar`, `Cancelar`, `Finalizar` | Concluido |
| T25.1 | Frontend - Index | Lista de contratos com busca/paginacao | Concluido |
| T25.2 | Frontend - Create | Formulario de criacao de contrato | Concluido |
| T25.3 | Frontend - Show | Detalhes, itens e acoes de status | Concluido |

### 6. Itens do Contrato

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T26 | Testes - Itens | Testes de adicao/remocao de itens | Concluido |
| T27 | Action - Adicionar | `Contrato/Item/Adicionar` | Concluido |
| T28 | Action - Remover | `Contrato/Item/Remover` | Concluido |
| T29 | Controllers | `Contrato/Item/Store`, `Update`, `Destroy` | Concluido |

### 7. Alocacao Automatica (FIFO)

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T30 | Testes - Alocacao | Testes de FIFO, multiplos lotes, disponibilidade | Concluido |
| T31 | Action - Alocar | `Alocacao/Alocar` (FIFO) | Concluido |
| T32 | Action - Liberar | `Alocacao/Liberar` (ao cancelar/finalizar) | Concluido |

### 8. Validacoes e Regras de Negocio

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T33 | Testes - Validacoes | Disponibilidade insuficiente, contrato ativo imutavel | Concluido |
| T34 | Exception - Indisponivel | `QuantidadeIndisponivelException` | Concluido |
| T35 | Exception - ContratoAtivo | `ContratoAtivoImutavelException` | Concluido |

### 9. Multi-tenant

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T36 | Testes - Isolamento | Usuario ve apenas dados do seu locador | Concluido |

---

## Migrations

Ordem de criacao (respeitando dependencias):

1. `create_planos_table`
2. `create_tenants_table`
3. `create_assinaturas_table`
4. `create_usuarios_table`
5. `create_locatarios_table`
6. `create_tipos_ativos_table`
7. `create_lotes_table`
8. `create_contratos_table`
9. `create_contrato_itens_table`
10. `create_alocacoes_lotes_table`

---

## Estrutura de Namespaces

### Controllers

```
App\Http\Controllers\
├── Locatario\
│   ├── Index
│   ├── Store
│   ├── Show
│   ├── Update
│   └── Destroy
├── TipoAtivo\
│   ├── Index
│   ├── Store
│   ├── Show
│   ├── Update
│   └── Destroy
├── Lote\
│   ├── Index
│   ├── Store
│   ├── Show
│   ├── Update
│   └── Destroy
└── Contrato\
    ├── Index
    ├── Store
    ├── Show
    ├── Ativar
    ├── Cancelar
    ├── Finalizar
    └── Item\
        ├── Store
        └── Destroy
```

### Actions

```
App\Actions\
├── Locatario\
│   ├── Criar
│   ├── Atualizar
│   └── Excluir
├── TipoAtivo\
│   ├── Criar
│   ├── Atualizar
│   └── Excluir
├── Lote\
│   ├── Criar
│   ├── Atualizar
│   └── Excluir
├── Contrato\
│   ├── Criar
│   ├── Ativar
│   ├── Cancelar
│   ├── Finalizar
│   └── Item\
│       ├── Adicionar
│       └── Remover
└── Alocacao\
    ├── Alocar
    └── Liberar
```

---

## Criterios de Aceitacao

```gherkin
Funcionalidade: Gestao de Contratos

  Cenario: Criar contrato em rascunho
    Dado que o usuario esta autenticado como cliente
    E existe um locatario cadastrado
    Quando cria um novo contrato
    Entao o contrato deve ter status "rascunho"
    E nenhum lote deve ser alocado

  Cenario: Adicionar item ao contrato rascunho
    Dado que existe um contrato em rascunho
    E existe um tipo de ativo "Placa EVA" com 20 unidades disponiveis
    Quando adiciona 15 unidades de "Placa EVA" ao contrato
    Entao o item deve ser adicionado
    E a disponibilidade dos lotes NAO deve ser alterada (ainda rascunho)

  Cenario: Ativar contrato com alocacao FIFO
    Dado que existe um contrato em rascunho com 15 "Placa EVA"
    E existe Lote A (mais antigo) com 12 disponiveis
    E existe Lote B com 10 disponiveis
    Quando ativa o contrato
    Entao o contrato deve ter status "ativo"
    E deve alocar 12 do Lote A
    E deve alocar 3 do Lote B
    E Lote A deve ter 0 disponiveis
    E Lote B deve ter 7 disponiveis

  Cenario: Tentar ativar contrato sem disponibilidade
    Dado que existe um contrato em rascunho com 30 "Placa EVA"
    E a disponibilidade total e de apenas 22 unidades
    Quando tenta ativar o contrato
    Entao deve retornar erro "Quantidade indisponivel"
    E o contrato deve permanecer em rascunho

  Cenario: Cancelar contrato ativo
    Dado que existe um contrato ativo com 15 "Placa EVA" alocadas
    Quando cancela o contrato
    Entao o contrato deve ter status "cancelado"
    E as 15 unidades devem ser liberadas para os lotes de origem

  Cenario: Tentar editar contrato ativo
    Dado que existe um contrato ativo
    Quando tenta adicionar ou remover itens
    Entao deve retornar erro "Contrato ativo nao pode ser alterado"

  Cenario: Multi-tenant isolamento
    Dado que existem dois tenants A e B
    E tenant A tem 3 contratos
    E tenant B tem 2 contratos
    Quando usuario do tenant A lista contratos
    Entao deve ver apenas os 3 contratos do tenant A
```

---

## Decisoes Tecnicas

### D01 - Status do Contrato como Enum PHP

**Decisao:** Usar PHP Enum para status (nao enum no banco)

```php
enum StatusContrato: string
{
    case Rascunho = 'rascunho';
    case Ativo = 'ativo';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';
}
```

### D02 - Alocacao Transacional

**Decisao:** Usar DB::transaction para garantir consistencia na alocacao

**Justificativa:** Evitar inconsistencias se houver falha no meio da alocacao de multiplos lotes.

### D03 - Soft Delete em Contratos

**Decisao:** Usar soft delete em contratos para manter historico

**Justificativa:** Contratos sao documentos legais e nao devem ser excluidos fisicamente.

---

## Artefatos

### Documentacao

| Documento | Caminho |
|-----------|---------|
| Funcionalidade | `docs/analises/features/contratos.md` |
| Modelo de Dados | `docs/analises/modelo-dados.md` |
| Milestone | `docs/MILESTONES.md` |

---

## Dependencias

- [x] Sprint 01 concluida (Landing Page)
- [x] Docker configurado com Laravel
- [x] PostgreSQL configurado

---

## Checklist de Conclusao

- [x] Todas as migrations criadas e rodadas
- [x] Models com relacionamentos funcionando
- [x] Factories e seeders criados
- [x] Todos os testes passando (73 testes, 190 assertions)
- [x] Actions implementadas
- [x] Controllers implementados
- [x] Rotas da API configuradas
- [x] Documentacao atualizada
- [x] Codigo revisado e commitado
- [x] Tag `docs-v1.0.0` criada
- [x] Estrutura do frontend implementada
- [x] Frontend de Contratos integrado com API

---

## Proximos Passos (apos Sprint 02)

1. Frontend Vue para gestao de contratos
2. Autenticacao e controle de acesso
3. Dashboard do tenant
