# Frontend Vue.js

> **Stack:** Vue 3 + TypeScript + Tailwind v4 + Pinia + Reka-UI
> **Build Tool:** Vite
> **Atualizado:** 2025-02-12

---

## Estrutura de Pastas

```
front/src/
├── types/                      # Tipos TypeScript
│   ├── index.ts                # Re-exports
│   ├── api.ts                  # Respostas da API
│   ├── models.ts               # Modelos de dominio
│   └── forms.ts                # Tipos de formularios
├── lib/
│   ├── utils.ts                # Utilitarios (cn, cva)
│   └── api.ts                  # Cliente HTTP fetch
├── stores/
│   ├── auth.ts                 # Autenticacao (user, token, locador)
│   └── notification.ts         # Toasts/alertas
├── composables/
│   ├── index.ts                # Re-exports
│   ├── useAuth.ts              # Wrapper para auth store
│   ├── useApi.ts               # Requisicoes paginadas
│   ├── useForm.ts              # Gerenciamento de formularios
│   └── useNotification.ts      # Wrapper para notification store
├── router/
│   ├── index.ts                # Configuracao de rotas
│   └── guards.ts               # Auth guards
├── layouts/
│   ├── AuthLayout.vue          # Layout para login/registro
│   └── AppLayout.vue           # Layout do dashboard
├── components/
│   ├── ui/                     # Componentes base (design system)
│   │   ├── button/
│   │   ├── input/
│   │   ├── label/
│   │   ├── select/
│   │   ├── dialog/
│   │   ├── table/
│   │   ├── pagination/
│   │   ├── toast/
│   │   ├── badge/
│   │   ├── dropdown/
│   │   └── spinner/
│   ├── app/                    # Componentes da aplicacao
│   │   ├── Sidebar.vue
│   │   ├── Header.vue
│   │   ├── UserMenu.vue
│   │   └── StatCard.vue
│   └── forms/                  # Componentes de formulario
│       ├── FormField.vue
│       ├── FormGroup.vue
│       └── FormActions.vue
└── views/
    ├── HomeView.vue            # Landing page
    ├── NotFoundView.vue        # 404
    ├── auth/
    │   ├── LoginView.vue
    │   └── RegisterView.vue
    └── app/
        ├── DashboardView.vue
        ├── tipos-ativos/
        │   ├── IndexView.vue
        │   ├── CreateView.vue
        │   └── EditView.vue
        ├── lotes/
        │   ├── IndexView.vue
        │   ├── CreateView.vue
        │   └── EditView.vue
        ├── locatarios/
        │   ├── IndexView.vue
        │   ├── CreateView.vue
        │   └── EditView.vue
        └── contratos/
            ├── IndexView.vue
            ├── CreateView.vue
            └── ShowView.vue
```

---

## Decisoes de Design

| Decisao | Escolha | Justificativa |
|---------|---------|---------------|
| HTTP Client | Native `fetch` wrapper | Bundle menor, sem dependencias extras |
| Validacao | Custom `useForm` | Controle total, principios SOLID |
| Dialog/Modal | Reka-UI Dialog | Ja disponivel, acessivel (WAI-ARIA) |
| Table | Custom com CVA | Consistencia com design system |
| Rotas | Nested routes | Layouts separados (auth vs app) |
| State | Pinia | Recomendado pelo Vue, TypeScript nativo |

---

## Rotas

### Publicas

| Path | Nome | View |
|------|------|------|
| `/` | home | HomeView |
| `/auth/login` | login | LoginView |
| `/auth/registrar` | register | RegisterView |

### Protegidas (requiresAuth)

| Path | Nome | View |
|------|------|------|
| `/app` | dashboard | DashboardView |
| `/app/tipos-ativos` | tipos-ativos.index | IndexView |
| `/app/tipos-ativos/criar` | tipos-ativos.create | CreateView |
| `/app/tipos-ativos/:id/editar` | tipos-ativos.edit | EditView |
| `/app/lotes` | lotes.index | IndexView |
| `/app/lotes/criar` | lotes.create | CreateView |
| `/app/lotes/:id/editar` | lotes.edit | EditView |
| `/app/locatarios` | locatarios.index | IndexView |
| `/app/locatarios/criar` | locatarios.create | CreateView |
| `/app/locatarios/:id/editar` | locatarios.edit | EditView |
| `/app/contratos` | contratos.index | IndexView |
| `/app/contratos/criar` | contratos.create | CreateView |
| `/app/contratos/:id` | contratos.show | ShowView |

---

## Cliente HTTP

O cliente HTTP (`lib/api.ts`) usa `fetch` nativo com:

- Token Bearer automatico (localStorage)
- Tratamento de erros padronizado
- Tipagem generica para respostas

```typescript
import api from '@/lib/api'

// GET
const response = await api.get<PaginatedResponse<Lote>>('/lotes', { page: 1 })

// POST
const response = await api.post<ApiResponse<Lote>>('/lotes', { nome: 'Lote A' })

// PUT
const response = await api.put<ApiResponse<Lote>>('/lotes/1', { nome: 'Lote B' })

// DELETE
await api.delete('/lotes/1')
```

---

## Stores

### Auth Store

Gerencia autenticacao do usuario:

```typescript
const authStore = useAuthStore()

// Estado
authStore.user        // User | null
authStore.locador     // Pessoa | null
authStore.token       // string | null
authStore.isLoading   // boolean

// Getters
authStore.isAuthenticated  // boolean
authStore.isAdmin          // boolean
authStore.userName         // string
authStore.locadorId        // string | null

// Actions
await authStore.login(email, password)
await authStore.logout()
await authStore.fetchMe()
await authStore.initialize()
```

### Notification Store

Gerencia toasts/alertas:

```typescript
const notifications = useNotificationStore()

notifications.success('Titulo', 'Mensagem')
notifications.error('Erro', 'Algo deu errado')
notifications.warning('Atencao', 'Cuidado')
notifications.info('Info', 'Informacao')
```

---

## Composables

### useAuth

Wrapper conveniente para auth store com navegacao:

```typescript
const { login, logout, isAuthenticated, user } = useAuth()

await login(email, password)  // Navega para dashboard
await logout()                // Navega para login
```

### useForm

Gerenciamento de formularios com validacao:

```typescript
const form = useForm({
  initialValues: { nome: '', email: '' },
  validate(values) {
    const errors = {}
    if (!values.nome) errors.nome = 'Nome obrigatorio'
    return errors
  },
  async onSubmit(values) {
    await api.post('/endpoint', values)
  }
})

// No template
<form @submit="form.handleSubmit">
  <input v-model="form.values.nome" />
  <span v-if="form.hasError('nome')">{{ form.getError('nome') }}</span>
</form>
```

### useApi / usePaginatedApi

Requisicoes com loading state:

```typescript
const { data, isLoading, error, execute } = useApi<Lote>('/lotes/1')

const {
  data,
  pagination,
  isLoading,
  goToPage,
  refresh
} = usePaginatedApi<Lote>('/lotes')
```

---

## Componentes UI

Todos os componentes seguem o padrao:

- CVA para variantes
- Props tipadas
- Slots para composicao
- Acessibilidade (WAI-ARIA)

### Exemplo: Button

```vue
<Button variant="primary" size="lg" :disabled="loading">
  <Spinner v-if="loading" size="sm" />
  Salvar
</Button>
```

### Exemplo: Dialog

```vue
<Dialog v-model:open="isOpen" title="Confirmar">
  <p>Deseja continuar?</p>
  <DialogFooter>
    <Button variant="outline" @click="isOpen = false">Cancelar</Button>
    <Button @click="confirm">Confirmar</Button>
  </DialogFooter>
</Dialog>
```

### Exemplo: Table

```vue
<Table>
  <TableHeader>
    <TableRow>
      <TableHead>Nome</TableHead>
      <TableHead>Status</TableHead>
    </TableRow>
  </TableHeader>
  <TableBody>
    <TableRow v-for="item in items" :key="item.id">
      <TableCell>{{ item.nome }}</TableCell>
      <TableCell>
        <Badge :variant="item.ativo ? 'success' : 'secondary'">
          {{ item.ativo ? 'Ativo' : 'Inativo' }}
        </Badge>
      </TableCell>
    </TableRow>
    <TableEmpty v-if="!items.length" :colspan="2">
      Nenhum registro encontrado
    </TableEmpty>
  </TableBody>
</Table>
```

---

## Variaveis de Ambiente

Arquivo `.env`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=Grapo
```

---

## Comandos

```bash
# Desenvolvimento
npm run dev

# Build
npm run build

# Type check
npm run type-check

# Lint
npm run lint
```

---

## Proximos Passos

1. [ ] Integrar views com endpoints da API
2. [ ] Implementar CRUD completo de Tipos de Ativos
3. [ ] Implementar CRUD completo de Lotes
4. [ ] Implementar CRUD completo de Locatarios
5. [ ] Implementar fluxo de Contratos
6. [ ] Adicionar testes E2E com Playwright
