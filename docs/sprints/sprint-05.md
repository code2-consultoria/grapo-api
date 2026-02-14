# Sprint 05 - Assinaturas e Pagamentos

> **Periodo:** 2025-02-14
> **Status:** Concluída
> **Milestone:** M4 - Monetização
> **Objetivo:** Implementar sistema de assinaturas com integração Stripe

---

## Backlog da Sprint

### Features

| ID | Feature | Prioridade | Status |
|----|---------|------------|--------|
| F01 | Listagem de Planos | Alta | ✅ Concluído |
| F02 | Checkout com Stripe | Alta | ✅ Concluído |
| F03 | Gestão de Assinaturas | Alta | ✅ Concluído |
| F04 | Webhooks Stripe | Alta | ✅ Concluído |
| F05 | Stripe Connect Express | Alta | ✅ Concluído |
| F06 | Pagamento de Contratos | Alta | ✅ Concluído |

---

## Planos Definidos

| Plano | Duração | Valor |
|-------|---------|-------|
| Trimestral | 3 meses | R$ 75,00 |
| Semestral | 6 meses | R$ 140,00 |
| Anual | 12 meses | R$ 250,00 |

---

## Tarefas

### 1. Setup Stripe

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T01 | Instalar Cashier | laravel/cashier | ✅ Concluído |
| T02 | Configurar .env | STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET | ✅ Concluído |
| T03 | Migration Stripe | Tabelas do Cashier (customizadas) | ✅ Concluído |

### 2. Backend - Planos

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T04 | Testes - Planos | 6 testes passando | ✅ Concluído |
| T05 | Controller Index | `Plano/Index` | ✅ Concluído |
| T06 | Controller Show | `Plano/Show` | ✅ Concluído |
| T07 | Rotas | GET /planos, GET /planos/{id} | ✅ Concluído |

### 3. Backend - Assinaturas

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T08 | Testes - Assinaturas | 11 testes passando | ✅ Concluído |
| T09 | Action - Assinar | Checkout direto no controller | ✅ Concluído |
| T10 | Action - Cancelar | Cancelamento no controller | ✅ Concluído |
| T11 | Controller Index | `Assinatura/Stripe/Index` | ✅ Concluído |
| T12 | Controller Checkout | `Assinatura/Stripe/Checkout` | ✅ Concluído |
| T13 | Controller Status | `Assinatura/Stripe/Status` | ✅ Concluído |
| T14 | Controller Cancel | `Assinatura/Stripe/Cancelar` | ✅ Concluído |
| T15 | Rotas | /assinaturas/* | ✅ Concluído |

### 4. Backend - Stripe Connect

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T16 | Migration Connect | Campos stripe_account_id, etc em pessoas | ✅ Concluído |
| T17 | Testes Connect | 8 testes passando | ✅ Concluído |
| T18 | Controller Onboard | `Stripe/Connect/Onboard` | ✅ Concluído |
| T19 | Controller Status | `Stripe/Connect/Status` | ✅ Concluído |
| T20 | Controller Dashboard | `Stripe/Connect/Dashboard` | ✅ Concluído |
| T21 | Controller Refresh | `Stripe/Connect/Refresh` | ✅ Concluído |
| T22 | Rotas | /stripe/connect/* | ✅ Concluído |

### 5. Backend - Pagamento de Contratos

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T23 | Migration Contrato | Campos stripe_subscription_id, etc | ✅ Concluído |
| T24 | Testes Pagamento | 11 testes passando | ✅ Concluído |
| T25 | Controller Store | `Contrato/Pagamento/Store` | ✅ Concluído |
| T26 | Controller Show | `Contrato/Pagamento/Show` | ✅ Concluído |
| T27 | Controller Destroy | `Contrato/Pagamento/Destroy` | ✅ Concluído |
| T28 | Rotas | /contratos/{id}/pagamento-stripe | ✅ Concluído |

### 6. Backend - Webhooks

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T29 | Controller Webhook | `Stripe/Webhook/Handler` | ✅ Concluído |
| T30 | Handler checkout.session.completed | Ativa assinatura | ✅ Concluído |
| T31 | Handler invoice.paid | Registra pagamento | ✅ Concluído |
| T32 | Handler customer.subscription.deleted | Cancela assinatura | ✅ Concluído |
| T33 | Handler account.updated | Atualiza status Connect | ✅ Concluído |
| T34 | Handler invoice.payment_failed | Registra falha | ✅ Concluído |
| T35 | Comando stripe:webhook | Auto-registro de webhook | ✅ Concluído |
| T36 | Value Object StripeConnectConfig | Configuração Stripe Connect | ✅ Concluído |
| T37 | Testes Webhook | 7 testes | ✅ Concluído |

### 7. Frontend - Planos

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T38 | Seleção de plano no registro | RegisterView.vue com planos | ✅ Concluído |
| T39 | Dialog de troca de plano | Perfil com seleção de plano | ✅ Concluído |
| T40 | Trial no registro | Opção de trial 7 dias | ✅ Concluído |

### 8. Frontend - Assinatura

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T41 | Seção de assinatura no perfil | IndexView.vue com status | ✅ Concluído |
| T42 | Alerta de expiração | Dashboard com warning | ✅ Concluído |
| T43 | Cancelamento de assinatura | Dialog de confirmação | ✅ Concluído |

### 10. Frontend - Majoração

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T48 | Seção de majoração no perfil | IndexView.vue | ✅ Concluído |
| T49 | Input de percentual | Validação e salvamento | ✅ Concluído |

### 9. Frontend - Stripe Connect

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T44 | View - Perfil com Stripe Connect | perfil/IndexView.vue | ✅ Concluído |
| T45 | Componente Status Connect | Badge e ações no perfil | ✅ Concluído |
| T46 | Componente pagamento no contrato | ContratoPagamentoStripe.vue | ✅ Concluído |
| T47 | Rota /app/perfil | Rota e menu do usuário | ✅ Concluído |

---

## Fluxo de Checkout

```
1. Locador acessa /app/planos
2. Seleciona um plano
3. POST /assinaturas { plano_id }
4. Backend cria Stripe Checkout Session
5. Retorna URL do Checkout
6. Frontend redireciona para Stripe
7. Usuário paga
8. Stripe envia webhook checkout.session.completed
9. Backend ativa assinatura
10. Stripe redireciona para /app/assinatura?success=true
```

## Fluxo de Cancelamento

```
1. Locador acessa /app/assinatura
2. Clica "Cancelar assinatura"
3. POST /assinaturas/{id}/cancelar
4. Backend cancela no Stripe
5. Status atualizado para 'cancelada'
```

## Fluxo de Onboarding Connect (Locador recebe pagamentos)

```
1. Locador acessa /app/configuracoes/stripe
2. Clica "Configurar Stripe Connect"
3. POST /stripe/connect/onboard
4. Backend cria conta Express e retorna URL de onboarding
5. Frontend redireciona para Stripe
6. Locador completa onboarding no Stripe
7. Stripe redireciona para return_url
8. Frontend chama POST /stripe/connect/refresh
9. Backend atualiza status da conta
```

## Fluxo de Pagamento de Contrato (Locatário paga)

```
1. Locador ativa pagamento Stripe no contrato
2. POST /contratos/{id}/pagamento-stripe { dia_vencimento: 10 }
3. Backend cria Customer e Subscription na conta Connect do locador
4. Locatário recebe fatura mensal via Stripe
5. 100% do valor vai para conta do locador
```

---

## Configuração Técnica

### Tabelas Stripe (customizadas)

As tabelas do Cashier foram renomeadas para evitar conflito com a tabela `assinaturas` existente:

| Tabela Original | Tabela Customizada |
|-----------------|-------------------|
| customers (colunas) | pessoas (stripe_id, pm_type, pm_last_four, trial_ends_at) |
| subscriptions | stripe_subscriptions |
| subscription_items | stripe_subscription_items |

### Campos Stripe Connect (pessoas)

Consolidados em campo JSON `stripe_connect_config` com Value Object:

| Campo (JSON) | Tipo | Descrição |
|-------|------|-----------|
| account_id | string | ID da conta Connect Express |
| onboarding_complete | boolean | Onboarding concluído |
| charges_enabled | boolean | Pode receber pagamentos |
| payouts_enabled | boolean | Pode receber transferências |
| webhook_endpoint_id | string | ID do endpoint de webhook |
| webhook_secret | string | Secret do webhook |

**Value Object:** `App\ValueObjects\StripeConnectConfig` com Cast `App\Casts\StripeConnectConfigCast`

### Campos Pagamento Stripe (contratos)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| stripe_subscription_id | string | ID da assinatura recorrente |
| stripe_customer_id | string | ID do customer (locatário) |
| dia_vencimento | integer | Dia do mês para cobrança (1-28) |

### Models Customizados

- `App\Models\StripeSubscription` - Extende Cashier Subscription
- `App\Models\StripeSubscriptionItem` - Extende Cashier SubscriptionItem

### Foreign Key

Usando `pessoa_id` (UUID) ao invés de `user_id`, pois locadores (Pessoa) são os clientes que assinam.

### Currency

Configurado para BRL (Real Brasileiro) com locale pt_BR.

---

## Estrutura de Arquivos

### Backend
```
api/app/
├── Models/
│   ├── StripeSubscription.php      # ✅ Model customizado
│   └── StripeSubscriptionItem.php  # ✅ Model customizado
├── ValueObjects/
│   └── StripeConnectConfig.php     # ✅ Value Object Connect
├── Casts/
│   └── StripeConnectConfigCast.php # ✅ Cast para JSON
├── Http/
│   ├── Controllers/
│   │   ├── Plano/
│   │   │   ├── Index.php           # ✅ Lista planos ativos
│   │   │   └── Show.php            # ✅ Exibe plano
│   │   ├── Assinatura/Stripe/
│   │   │   ├── Index.php           # ✅ Lista assinaturas
│   │   │   ├── Checkout.php        # ✅ Cria checkout session
│   │   │   ├── Status.php          # ✅ Status da assinatura
│   │   │   └── Cancelar.php        # ✅ Cancela assinatura
│   │   ├── Stripe/
│   │   │   ├── Connect/
│   │   │   │   ├── Onboard.php     # ✅ Inicia onboarding Connect
│   │   │   │   ├── Status.php      # ✅ Status da conta Connect
│   │   │   │   ├── Refresh.php     # ✅ Atualiza status Connect
│   │   │   │   └── Dashboard.php   # ✅ Link para dashboard Stripe
│   │   │   └── Webhook/
│   │   │       └── Handler.php     # ✅ Processa webhooks Stripe
│   │   ├── Perfil/Majoracao/
│   │   │   ├── Show.php            # ✅ Retorna majoração atual
│   │   │   └── Update.php          # ✅ Atualiza majoração
│   │   └── Contrato/Pagamento/
│   │       ├── Store.php           # ✅ Cria pagamento recorrente
│   │       ├── Show.php            # ✅ Status do pagamento
│   │       └── Destroy.php         # ✅ Cancela pagamento
│   └── Resources/
│       ├── PlanoResource.php               # ✅ Resource de Plano
│       └── StripeSubscriptionResource.php  # ✅ Resource de Subscription
├── Console/Commands/
│   └── Stripe/
│       └── RegisterWebhook.php     # ✅ Comando stripe:webhook
├── Providers/
│   └── AppServiceProvider.php      # ✅ Configura Cashier
├── Middleware/
│   └── VerificaAssinatura.php      # ✅ Middleware de acesso
├── config/
│   └── assinatura.php              # ✅ Configuração de trial e acesso
└── tests/Feature/
    ├── PlanoTest.php               # ✅ 6 testes
    ├── Assinatura/StripeTest.php   # ✅ 11 testes
    ├── Locador/
    │   ├── DescontoComercialTest.php # ✅ 8 testes
    │   ├── AssinaturaAcessoTest.php  # ✅ 13 testes
    │   └── MajoracaoDiariaTest.php   # ✅ 8 testes
    └── Stripe/
        ├── ConnectTest.php         # ✅ 8 testes
        ├── ContratoPagamentoTest.php # ✅ 11 testes
        └── WebhookTest.php         # ✅ 7 testes
```

### Frontend
```
front/src/
├── views/
│   ├── app/
│   │   ├── perfil/
│   │   │   └── IndexView.vue        # ✅ Perfil com Stripe Connect, Assinatura, Majoração
│   │   ├── contratos/
│   │   │   └── ShowView.vue         # ✅ Atualizado com pagamento Stripe
│   │   └── DashboardView.vue        # ✅ Alerta de expiração de assinatura
│   └── auth/
│       └── RegisterView.vue         # ✅ Seleção de plano no registro
├── components/app/
│   ├── ContratoPagamentoStripe.vue  # ✅ Componente pagamento contrato
│   ├── ContratoPagamentos.vue       # ✅ Desconto comercial e valor_final
│   └── UserMenu.vue                 # ✅ Link para perfil
└── router/
    └── index.ts                     # ✅ Rota /app/perfil
```

---

## Webhooks Stripe

### Eventos Suportados

| Evento | Handler | Ação |
|--------|---------|------|
| account.updated | handleAccountUpdated | Atualiza status Connect do locador |
| checkout.session.completed | handleCheckoutSessionCompleted | Ativa assinatura da plataforma |
| customer.subscription.created | - | Registra nova assinatura |
| customer.subscription.updated | - | Atualiza dados da assinatura |
| customer.subscription.deleted | handleCustomerSubscriptionDeleted | Remove dados Stripe do contrato |
| invoice.paid | handleInvoicePaid | Registra pagamento do contrato |
| invoice.payment_failed | handleInvoicePaymentFailed | Registra falha de pagamento |

### Endpoint

```
POST /api/stripe/webhook
```

### Comando de Registro

```bash
# Registra webhook no Stripe e atualiza .env automaticamente
docker exec grapo-api php artisan stripe:webhook

# Com URL específica
docker exec grapo-api php artisan stripe:webhook --url=https://meusite.com/api/stripe/webhook

# Remove webhooks existentes antes de criar
docker exec grapo-api php artisan stripe:webhook --delete
```

### Variáveis de Ambiente

```env
STRIPE_WEBHOOK_SECRET=whsec_xxxxx  # Gerado automaticamente pelo comando
```

---

## Checklist de Conclusão

### Stripe Billing (Assinatura da Plataforma)
- [x] Laravel Cashier instalado
- [x] Variáveis de ambiente configuradas
- [x] Endpoints de planos funcionando
- [x] Checkout Session criando corretamente
- [x] Webhooks recebendo eventos
- [x] Handlers de eventos implementados
- [x] Cancelamento funcionando
- [x] Seleção de plano no registro
- [x] Gestão de assinatura no perfil

### Stripe Connect (Locador recebe pagamentos)
- [x] Onboarding de conta Connect Express
- [x] Verificação de status da conta
- [x] Link para dashboard Stripe
- [x] Refresh de status após onboarding
- [x] Value Object para configuração Connect
- [x] Webhook account.updated para atualizar status
- [x] Frontend de configuração Connect (StripeConnectView.vue)

### Pagamento de Contratos
- [x] Criação de assinatura recorrente
- [x] Cancelamento de pagamento
- [x] Validações (locador com Connect, contrato ativo, email)
- [x] Webhook invoice.paid para registrar pagamento
- [x] Webhook invoice.payment_failed para registrar falha
- [x] Webhook customer.subscription.deleted para cancelar
- [x] Frontend de ativação de pagamento (ContratoPagamentoStripe.vue)

### Webhooks
- [x] Handler de webhooks implementado
- [x] Comando stripe:webhook para auto-registro
- [x] Atualização automática do .env
- [x] Validação de assinatura Stripe
- [x] Testes de webhook

### Testes
- [x] 64+ testes API passando
  - PlanoTest: 6 testes
  - StripeTest: 11 testes
  - ConnectTest: 8 testes
  - ContratoPagamentoTest: 11 testes
  - WebhookTest: 7 testes
  - DescontoComercialTest: 8 testes
  - AssinaturaAcessoTest: 13 testes
  - MajoracaoDiariaTest: 8 testes

---

## Funcionalidades Adicionais (Sprint 05)

### Desconto Comercial nos Pagamentos

Permite aplicar desconto fixo nas parcelas de pagamento do contrato.

**Regras:**
- Valor fixo (não percentual)
- Máximo: valor da parcela
- Apenas para pagamentos pendentes
- Pode excluir, mas não alterar
- Campo calculado `valor_final = valor - desconto_comercial`

**Campos:**
| Campo | Tipo | Descrição |
|-------|------|-----------|
| desconto_comercial | decimal(10,2) | Valor do desconto |
| valor_final | accessor | Valor - desconto (calculado) |

**Endpoints:**
- `POST /contratos/{id}/pagamentos` - Aceita `desconto_comercial`
- `GET /contratos/{id}/pagamentos` - Retorna `valor_final`

**Testes:** 8 testes passando

---

### Controle de Acesso por Assinatura

Sistema de controle de acesso baseado em data limite.

**Regras:**
- Trial: 7 dias após registro (configurável via .env)
- Após pagamento: 60 dias de acesso (configurável via .env)
- Após cancelamento: 30 dias (configurável via .env)
- Middleware bloqueia acesso a contratos quando expirado
- Admin sempre tem acesso

**Campos (pessoas):**
| Campo | Tipo | Descrição |
|-------|------|-----------|
| data_limite_acesso | date | Data até quando pode acessar |

**Configuração (config/assinatura.php):**
```php
'trial_dias' => env('ASSINATURA_TRIAL_DIAS', 7),
'dias_apos_pagamento' => env('ASSINATURA_DIAS_APOS_PAGAMENTO', 60),
'dias_apos_cancelamento' => env('ASSINATURA_DIAS_APOS_CANCELAMENTO', 30),
```

**Middleware:** `VerificaAssinatura` aplicado nas rotas `/contratos/*`

**Frontend:**
- Seleção de plano no registro (RegisterView.vue)
- Status da assinatura no perfil (IndexView.vue)
- Alerta de expiração no dashboard (DashboardView.vue)

**Testes:** 13 testes passando

---

### Majoração da Diária

Permite configurar percentual de majoração para cálculo da diária sugerida.

**Fórmula:**
```
Diária = (Valor Mensal × (1 + Majoração%/100)) ÷ 30
```

**Regras:**
- Valor padrão: 10%
- Mínimo: 0% (não permite negativo)
- Configurável por locador no perfil

**Campos (pessoas):**
| Campo | Tipo | Descrição |
|-------|------|-----------|
| majoracao_diaria | decimal(5,2) | Percentual de majoração (default: 10.00) |

**Endpoints:**
- `GET /perfil/majoracao` - Retorna majoração atual
- `PUT /perfil/majoracao` - Atualiza majoração

**Frontend:**
- Seção "Majoração da Diária" no perfil (IndexView.vue)
- Input numérico com validação
- Explicação da fórmula

**Testes:** 8 testes passando
