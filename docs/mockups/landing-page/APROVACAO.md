# Aprovação de Mockups - Landing Page e App Grapo

> **Data de aprovação:** 2025-02-11
> **Status:** Aprovado com correções no desenvolvimento
> **Fonte:** Stitch

---

## Telas Recebidas

| # | Tela | Arquivo |
|---|------|---------|
| 1 | Landing Page Desktop | `landing_page_grapo_desktop/` |
| 2 | Criar Conta de Locador | `criar_conta_de_locador/` |
| 3 | Escolha seu Plano | `escolha_seu_plano/` |
| 4 | Finalizar Assinatura | `finalizar_assinatura/` |
| 5 | Login | `login_e_acesso_seguro/` |
| 6 | Dashboard de Relatórios | `dashboard_de_relatórios/` |
| 7 | Gestão de Contratos | `gestão_de_contratos/` |
| 8 | Cadastro de Novo Lote | `cadastro_de_novo_lote/` |
| 9 | Criar Novo Contrato | `criar_novo_contrato/` |
| 10 | Detalhes do Contrato | `detalhes_e_ações_do_contrato/` |
| 11 | Relatório de Lote e ROI | `relatório_de_lote_e_roi/` |

---

## Aprovado

- Estrutura e layout das telas
- Fluxo de navegação (cadastro → plano → pagamento)
- Funcionalidades representadas
- UX geral

---

## Correções a Aplicar no Desenvolvimento

### 1. Preços dos Planos

| Plano | Mockup | Correto |
|-------|--------|---------|
| Trimestral | R$ 149,70 | **R$ 75,00** (R$ 25/mês) |
| Semestral | R$ 239,40 | **R$ 140,00** (R$ 23,33/mês) |
| Anual | R$ 358,80 | **R$ 250,00** (R$ 20,83/mês) |

### 2. Recursos dos Planos

**Mockup:** Planos diferenciados (limites de ativos, suporte VIP)

**Correto:** Todos os planos iguais:
- Ativos ilimitados
- Contratos ilimitados
- Cobranças via Stripe
- Dashboard completo
- Alertas por e-mail
- Suporte por e-mail
- 7 dias de teste grátis

### 3. Terminologia

| Mockup | Correto |
|--------|---------|
| imóveis | ativos / equipamentos |
| carteira de imóveis | gestão de aluguéis |
| Aluguel Residencial | Aluguel de Equipamentos |

### 4. Paleta de Cores (Telas Internas)

| Mockup | Correto |
|--------|---------|
| Azul (#3B82F6) | Dourado (#D4AF37) |

**Paleta oficial:**

| Uso | Cor |
|-----|-----|
| Primária (CTAs, destaques) | `#D4AF37` (dourado) |
| Títulos | `#000000` (preto) |
| Texto | `#333333` (cinza escuro) |
| Fundo claro | `#FFFFFF` / `#F5F5F5` |
| Fundo escuro | `#1A1A1A` |
| Sucesso | `#22C55E` |
| Alerta | `#EAB308` |
| Erro | `#EF4444` |

---

## Histórico

| Data | Ação |
|------|------|
| 2025-02-11 | Mockups recebidos do Stitch |
| 2025-02-11 | Análise e identificação de correções |
| 2025-02-11 | Aprovação com correções no desenvolvimento |
