---
name: generate-docs
description: Gera ou atualiza documentação do projeto. Use para criar docs de API, README, guias de uso, ou documentação técnica.
argument-hint: "[api|readme|models|actions|all]"
context: fork
agent: general-purpose
allowed-tools: Read, Glob, Grep, Write
---

# Agente: Gerador de Documentação

Analisa o código-fonte e gera documentação técnica atualizada e consistente.

## Uso

- `/generate-docs api` - Documenta endpoints da API
- `/generate-docs models` - Documenta models e relacionamentos
- `/generate-docs actions` - Documenta Actions do projeto
- `/generate-docs readme` - Atualiza README principal
- `/generate-docs all` - Gera toda documentação

## Tipos de Documentação

### 1. Documentação de API (`api`)

Gera documentação dos endpoints REST em `docs/API.md`:

```markdown
# API Reference

## Autenticação

### POST /api/auth/login
Autentica usuário e retorna token.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "senha123"
}
```

**Response (200):**
```json
{
  "data": {
    "token": "1|abc...",
    "user": { "id": 1, "name": "João" }
  }
}
```

**Erros:**
- 401: Credenciais inválidas
- 422: Validação falhou
```

#### Processo:
1. Ler `routes/api.php`
2. Identificar controllers de cada rota
3. Analisar validações e responses
4. Gerar exemplos de request/response
5. Documentar códigos de erro

### 2. Documentação de Models (`models`)

Gera documentação em `docs/MODELS.md`:

```markdown
# Models

## Pessoa

Representa locadores, locatários e responsáveis.

**Tabela:** `pessoas`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | uuid | Identificador único |
| tipo | enum | locador, locatario, responsavel_fin, responsavel_adm |
| nome | string | Nome completo |
| locador_id | uuid? | FK para locador (null se for locador) |

**Relacionamentos:**
- `locador()` → belongsTo(Pessoa)
- `documentos()` → hasMany(Documento)
- `contratos()` → hasMany(Contrato)

**Scopes:**
- `locadores()` - Filtra apenas locadores
- `locatarios()` - Filtra apenas locatários
- `ativos()` - Filtra apenas ativos

**Accessors:**
- `tipo_pessoa` - Retorna PF ou PJ baseado nos documentos
```

#### Processo:
1. Ler todos os models em `app/Models/`
2. Analisar `$fillable`, `$casts`, relacionamentos
3. Ler migration correspondente para tipos de campo
4. Documentar scopes e accessors
5. Criar diagrama de relacionamentos (ASCII)

### 3. Documentação de Actions (`actions`)

Gera documentação em `docs/ACTIONS.md`:

```markdown
# Actions

## Contrato/Criar

Cria um novo contrato em rascunho.

**Namespace:** `App\Actions\Contrato\Criar`

**Parâmetros:**
| Nome | Tipo | Obrigatório | Descrição |
|------|------|-------------|-----------|
| locador | Pessoa | Sim | Locador do contrato |
| locatario | Pessoa | Sim | Locatário |
| dataInicio | DateTime | Sim | Data de início |
| dataTermino | DateTime | Sim | Data de término |

**Retorno:** `Contrato`

**Exceções:**
- Nenhuma

**Exemplo:**
```php
$criar = new Criar(
    locador: $locador,
    locatario: $locatario,
    dataInicio: new DateTime('2024-01-01'),
    dataTermino: new DateTime('2024-12-31')
);
$criar->handle();
$contrato = $criar->getContrato();
```
```

#### Processo:
1. Ler todos os arquivos em `app/Actions/`
2. Analisar construtor e método `handle()`
3. Identificar exceções lançadas
4. Documentar dependências
5. Gerar exemplos de uso

### 4. README (`readme`)

Atualiza `README.md` do projeto:

```markdown
# Grapo

Sistema de gestão de locação de ativos.

## Stack

- **Backend:** Laravel 12, PHP 8.3
- **Database:** PostgreSQL
- **Auth:** Laravel Sanctum (API Tokens)

## Instalação

```bash
git clone ...
cd grapo/api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Estrutura

```
api/
├── app/
│   ├── Actions/      # Lógica de negócio
│   ├── Models/       # Eloquent models
│   ├── Http/
│   │   └── Controllers/  # Invokable controllers
│   └── Services/     # Serviços auxiliares
├── routes/
│   └── api.php       # Rotas da API
└── tests/
    └── Feature/      # Testes de integração
```

## Documentação

- [API Reference](docs/API.md)
- [Models](docs/MODELS.md)
- [Actions](docs/ACTIONS.md)
```

## Formato de Saída

- Markdown bem formatado
- Exemplos de código com syntax highlighting
- Tabelas para dados estruturados
- Links entre documentos relacionados
- TOC (Table of Contents) para docs longos

## Instruções para o Agente

1. **Identificar** o tipo de documentação solicitado em $ARGUMENTS
2. **Explorar** código-fonte relevante usando Glob e Read
3. **Analisar** estrutura, tipos, relacionamentos
4. **Gerar** documentação seguindo os templates acima
5. **Salvar** nos arquivos apropriados em `docs/`
6. **Reportar** o que foi gerado/atualizado

## Arquivos de Saída

| Comando | Arquivo |
|---------|---------|
| api | `docs/API.md` |
| models | `docs/MODELS.md` |
| actions | `docs/ACTIONS.md` |
| readme | `README.md` |
| all | Todos acima |

## Boas Práticas

- Manter documentação sincronizada com código
- Incluir exemplos práticos
- Documentar edge cases e erros
- Usar linguagem clara e objetiva
- Atualizar TOC quando necessário
