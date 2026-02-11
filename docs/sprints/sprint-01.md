# Sprint 01 - Landing Page Grapo

> **Período:** 2025-02-11 a [definir]
> **Status:** Concluída
> **Objetivo:** Criar a landing page do Grapo com projeto Vue separado

## Backlog da Sprint

### Features

| ID | Feature | Prioridade | Status |
|----|---------|------------|--------|
| F01 | [Landing Page](../analises/features/landing-page.md) | Alta | Pendente |

---

## Tarefas

### 1. Setup do Projeto

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T01 | Criar projeto Vue | `npm create vue@latest` na pasta `front/` | Concluído |
| T02 | Configurar Tailwind CSS | Instalar e configurar com paleta Grapo | Concluído |
| T03 | Instalar shadcn/vue | Configurar componentes base (Button, Card) | Concluído |
| T04 | Configurar Lucide icons | Substituir Material Icons | Concluído |
| T05 | Configurar ESLint + Prettier | Padrões de código | Concluído |

### 2. Paleta de Cores

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T06 | Definir tokens no Tailwind | Cores customizadas do Grapo | Concluído |

**Cores a configurar:**

```javascript
colors: {
  primary: '#D4AF37',        // Dourado
  background: {
    light: '#F8F7F6',
    dark: '#12110D',
  },
  card: '#1E1B12',
  foreground: {
    DEFAULT: '#333333',
    muted: '#6B7280',
  }
}
```

### 3. Componentes da Landing Page

| ID | Tarefa | Componente | Descrição | Status |
|----|--------|------------|-----------|--------|
| T07 | Navbar | `Navbar.vue` | Logo + links + CTA "Começar" | Concluído |
| T08 | Hero | `Hero.vue` | Título, subtítulo, CTA, imagem | Concluído |
| T09 | Problems | `Problems.vue` | 3 cards de problemas | Concluído |
| T10 | Features | `Features.vue` | 6 cards de funcionalidades | Concluído |
| T11 | Pricing | `Pricing.vue` | 3 planos (Trimestral, Semestral, Anual) | Concluído |
| T12 | Footer | `Footer.vue` | Links, redes sociais, copyright | Concluído |

### 4. Página e Roteamento

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T13 | Criar página index | Montar landing com componentes | Concluído |
| T14 | Configurar Vue Router | Rota `/` para landing page | Concluído |

### 5. Correções do Mockup

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T15 | Corrigir preços | Trimestral R$75, Semestral R$140, Anual R$250 | Concluído |
| T16 | Igualar planos | Todos com mesmos recursos (ilimitados) | Concluído |
| T17 | Revisar textos | Garantir "ativos/equipamentos" (não imóveis) | Concluído |

### 6. Responsividade e Testes

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T18 | Mobile first | Garantir responsividade em todas as seções | Concluído |
| T19 | Testar navegadores | Chrome, Firefox, Safari | Concluído |
| T20 | Lighthouse | Performance, Acessibilidade, SEO | Concluído |

---

## Critérios de Aceitação

```gherkin
Funcionalidade: Landing Page Grapo

  Cenário: Visitante acessa a landing page
    Dado que o visitante acessa grapo.app
    Quando a página carrega
    Então deve exibir o hero com título "Transforme seus equipamentos parados em renda recorrente"
    E deve exibir o botão "Criar minha conta grátis"

  Cenário: Visitante visualiza os planos
    Dado que o visitante está na landing page
    Quando rola até a seção de planos
    Então deve ver 3 planos: Trimestral, Semestral e Anual
    E o plano Trimestral deve custar R$ 75,00
    E o plano Semestral deve custar R$ 140,00
    E o plano Anual deve custar R$ 250,00
    E o plano Anual deve estar destacado como "Mais Popular"

  Cenário: Visitante clica no CTA principal
    Dado que o visitante está na landing page
    Quando clica em "Criar minha conta grátis"
    Então deve ser direcionado para a página de cadastro (futura sprint)

  Cenário: Landing page responsiva
    Dado que o visitante acessa de um dispositivo móvel
    Quando a página carrega
    Então todas as seções devem estar visíveis e legíveis
    E o menu deve estar adaptado para mobile
```

---

## Decisões Técnicas

### D01 - Framework Vue

**Decisão:** Usar Vue 3 com Composition API + `<script setup>`

**Justificativa:** Sintaxe mais limpa, melhor suporte a TypeScript (se necessário no futuro), recomendação oficial.

### D02 - Gerenciador de Pacotes

**Decisão:** Usar npm (padrão do projeto Laravel)

**Justificativa:** Manter consistência com o backend.

### D03 - Estrutura de Componentes

**Decisão:** Separar componentes da landing em pasta própria (`components/landing/`)

**Justificativa:** Facilita manutenção e evita misturar com componentes do app (futuro).

### D04 - Imagens

**Decisão:** Usar imagens locais otimizadas (não URLs externas como no Stitch)

**Justificativa:** Performance, controle, não depender de serviços externos.

---

## Artefatos

### Mockups (Aprovados)

| Tela | Arquivo |
|------|---------|
| Landing Page Desktop | `docs/mockups/landing-page/stitch/landing_page_grapo_desktop/` |

### Documentação

| Documento | Caminho |
|-----------|---------|
| Análise da Feature | `docs/analises/features/landing-page.md` |
| Aprovação do Mockup | `docs/mockups/landing-page/APROVACAO.md` |

---

## Dependências

- [ ] Nenhuma dependência de outras features

---

## Checklist de Conclusão

- [x] Projeto Vue criado e configurado
- [x] Tailwind + shadcn/vue funcionando
- [x] Todos os componentes da landing implementados
- [x] Preços e textos corrigidos conforme documentação
- [x] Responsivo em mobile, tablet e desktop
- [x] Lighthouse score > 90 em Performance e Acessibilidade
- [ ] Código revisado e commitado

## Resultados Lighthouse (Build de Produção)

| Categoria | Score |
|-----------|-------|
| Performance | 100 |
| Accessibility | 97 |
| Best Practices | 100 |
| SEO | 100 |
