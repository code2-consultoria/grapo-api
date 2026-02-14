# Sprint 03 - Dashboard

> **Periodo:** 2025-02-13 a 2025-02-13
> **Status:** Concluida
> **Milestone:** M2 - Dashboard
> **Objetivo:** Implementar dashboard com metricas financeiras e operacionais

---

## Backlog da Sprint

### Features

| ID | Feature | Prioridade | Status |
|----|---------|------------|--------|
| F01 | Metricas Financeiras | Alta | Concluido |
| F02 | Metricas Operacionais | Alta | Concluido |
| F03 | Alertas | Media | Concluido |

---

## Metricas Implementadas

### Financeiras
- **Receita Total**: Soma dos valores de contratos ativos
- **Contratos Ativos**: Quantidade de contratos em status "ativo"
- **Contratos a Vencer**: Contratos que vencem nos proximos 30 dias
- **Receita Mensal**: Valor total dividido por 12 (media mensal)

### Operacionais
- **Estoque Disponivel**: Quantidade total de itens disponiveis
- **Taxa de Ocupacao**: % de itens alocados vs total
- **Top 5 Ativos**: Ativos mais alugados (por quantidade)
- **Lotes por Status**: Distribuicao de lotes (disponivel/indisponivel/esgotado)

### Alertas
- Contratos vencendo em 7 dias (warning)
- Estoque baixo - menos de 5 unidades (info)
- Ativos esgotados (destructive)

---

## Tarefas

### 1. Backend - Endpoint Dashboard

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T01 | Query - Dashboard | `Queries/Dashboard/Metricas` | Concluido |
| T02 | Controller | `Controllers/Dashboard/Index` | Concluido |
| T03 | Testes | 11 testes das metricas | Concluido |

### 2. Frontend - Dashboard View

| ID | Tarefa | Descricao | Status |
|----|--------|-----------|--------|
| T04 | Cards de Metricas | StatCards com valores financeiros e operacionais | Concluido |
| T05 | Barra de Ocupacao | Barra de progresso visual | Concluido |
| T06 | Lista Top Ativos | Ranking com medalhas | Concluido |
| T07 | Alertas | Cards coloridos por tipo | Concluido |
| T08 | Contratos a Vencer | Lista clicavel com badges | Concluido |

---

## Estrutura de Arquivos

### Backend
```
api/app/
├── Queries/Dashboard/
│   └── Metricas.php
├── Http/Controllers/Dashboard/
│   └── Index.php
└── tests/Feature/Dashboard/
    └── MetricasTest.php (11 testes)
```

### Frontend
```
front/src/
├── views/app/
│   └── DashboardView.vue
└── types/
    └── api.ts (tipos DashboardData, etc)
```

---

## Layout Final

```
+--------------------------------------------------+
|                    DASHBOARD                      |
+--------------------------------------------------+
|  [Receita]  [Contratos]  [Estoque]  [Ocupacao]   |
|  R$ 5.488     3            42         76%        |
+--------------------------------------------------+
|  ALERTAS                                          |
|  [!] Contratos a vencer    [i] Estoque baixo     |
+--------------------------------------------------+
|  Contratos a Vencer (30d)  |  Top 5 Ativos       |
|  - CTR-0001 (7 dias)       |  1. Tatame EVA (42) |
|  - CTR-0002 (15 dias)      |  2. Cadeira (20)    |
+--------------------------------------------------+
|  OCUPACAO DO ESTOQUE                              |
|  [==========        ] 76% ocupado                 |
|  Disponivel: 42  Indisponivel: 0  Esgotado: 1    |
+--------------------------------------------------+
```

---

## Checklist de Conclusao

- [x] Endpoint GET /api/dashboard implementado
- [x] Testes do endpoint passando (11 testes, 34 assertions)
- [x] Frontend com cards de metricas
- [x] Barra de ocupacao visual
- [x] Lista de alertas coloridos
- [x] Contratos a vencer com links
- [x] Top ativos com ranking
- [x] Tipos TypeScript atualizados
- [x] Documentacao atualizada

---

## Proximos Passos

- Implementar graficos de evolucao (futuro)
- Adicionar filtros por periodo (futuro)
- Exportar relatorios (futuro)
