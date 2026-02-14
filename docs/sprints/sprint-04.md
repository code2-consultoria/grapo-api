# Sprint 04 - Autenticação Completa

> **Periodo:** 2025-02-13 a 2025-02-13
> **Status:** Concluida
> **Milestone:** M3 - Autenticação
> **Objetivo:** Completar sistema de autenticação com registro e recuperação de senha

---

## Backlog da Sprint

### Features

| ID | Feature | Prioridade | Status |
|----|---------|------------|--------|
| F01 | Registro de Usuários | Alta | Concluido |
| F02 | Recuperação de Senha | Alta | Concluido |

---

## Tarefas

### 1. Backend - Registro de Usuários

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T01 | Testes - Registro | 12 testes de criação de usuário via API | Concluido |
| T02 | Action - Registrar | `Auth/Registrar` - cria user + locador | Concluido |
| T03 | Controller - Register | `Auth/Api/Register` | Concluido |
| T04 | Rota | POST /auth/register | Concluido |

### 2. Backend - Recuperação de Senha

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T05 | Testes - Forgot | 4 testes de envio de email | Concluido |
| T06 | Testes - Reset | 7 testes de reset com token | Concluido |
| T07 | Controller - Forgot | `Auth/Api/ForgotPassword` | Concluido |
| T08 | Controller - Reset | `Auth/Api/ResetPassword` | Concluido |
| T09 | Rotas | POST /auth/forgot-password, POST /auth/reset-password | Concluido |

### 3. Frontend - Recuperação de Senha

| ID | Tarefa | Descrição | Status |
|----|--------|-----------|--------|
| T10 | View - Forgot | ForgotPasswordView.vue | Concluido |
| T11 | View - Reset | ResetPasswordView.vue | Concluido |
| T12 | Rotas | /auth/esqueci-senha, /auth/redefinir-senha/:token | Concluido |
| T13 | Link no Login | "Esqueci minha senha" | Concluido |
| T14 | Auto-login no Registro | Usar token do registro | Concluido |

---

## Estrutura de Arquivos

### Backend
```
api/app/
├── Actions/Auth/
│   └── Registrar.php
├── Http/Controllers/Auth/Api/
│   ├── Register.php
│   ├── ForgotPassword.php
│   └── ResetPassword.php
└── tests/Feature/Auth/
    ├── RegistroTest.php (12 testes)
    └── RecuperacaoSenhaTest.php (11 testes)
```

### Frontend
```
front/src/
├── views/auth/
│   ├── LoginView.vue (atualizado com link)
│   ├── RegisterView.vue (atualizado com auto-login)
│   ├── ForgotPasswordView.vue (novo)
│   └── ResetPasswordView.vue (novo)
├── stores/
│   └── auth.ts (atualizado com setAuthData)
└── router/
    └── index.ts (novas rotas)
```

---

## Endpoints da API

| Método | Rota | Descrição | Auth |
|--------|------|-----------|------|
| POST | /auth/register | Registra usuário + locador | Não |
| POST | /auth/forgot-password | Envia email de recuperação | Não |
| POST | /auth/reset-password | Reseta senha com token | Não |

---

## Fluxo de Registro

```
1. Usuário preenche formulário (nome, email, senha)
2. POST /auth/register
3. Backend cria:
   - User (papel: cliente, ativo: true)
   - Pessoa (tipo: locador, nome, email)
   - VinculoTime (liga user ao locador)
4. Retorna token + user + locador
5. Frontend salva token e redireciona para dashboard
```

## Fluxo de Recuperação de Senha

```
1. Usuário clica "Esqueci minha senha"
2. Preenche email em /auth/esqueci-senha
3. POST /auth/forgot-password
4. Backend envia email com link + token
5. Usuário clica no link
6. Frontend abre /auth/redefinir-senha/:token
7. Usuário define nova senha
8. POST /auth/reset-password { token, email, password }
9. Backend atualiza senha
10. Redireciona para login
```

---

## Testes

### Registro (12 testes)
- Registro com dados válidos
- Criação automática de locador
- Vinculação user-locador
- Token de autenticação
- Validações (nome, email, senha, confirmação)
- Email duplicado

### Recuperação de Senha (11 testes)
- Envio de link por email
- Email inexistente (retorna sucesso por segurança)
- Reset com token válido
- Token inválido/expirado
- Email diferente do token
- Validações de senha
- Token invalidado após uso

---

## Checklist de Conclusão

- [x] Endpoint POST /auth/register implementado
- [x] Registro cria user + locador + vínculo
- [x] 12 testes de registro passando
- [x] Endpoint POST /auth/forgot-password implementado
- [x] Endpoint POST /auth/reset-password implementado
- [x] 11 testes de recuperação passando
- [x] Frontend ForgotPasswordView funcionando
- [x] Frontend ResetPasswordView funcionando
- [x] Link "Esqueci minha senha" no login
- [x] Auto-login após registro
- [x] Store auth atualizado
- [x] Rotas do router configuradas
- [x] Documentação atualizada
