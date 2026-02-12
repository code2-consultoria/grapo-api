---
name: openapi
description: Gera especificação OpenAPI 3.0 (Swagger) em YAML a partir do código-fonte. Use para criar documentação de API padronizada.
argument-hint: "[--output=docs/openapi.yaml]"
context: fork
agent: general-purpose
allowed-tools: Read, Glob, Grep, Write
---

# Agente: Gerador de OpenAPI

Analisa rotas, controllers e models para gerar especificação OpenAPI 3.0 completa em formato YAML.

## Uso

- `/openapi` - Gera `docs/openapi.yaml`
- `/openapi --output=api/public/docs/api.yaml` - Especifica arquivo de saída

## Processo de Geração

### 1. Coletar Informações

```
1. Ler routes/api.php
   └── Identificar rotas, métodos HTTP, middlewares

2. Para cada rota, ler o Controller
   └── Analisar validações (Request)
   └── Analisar responses (JsonResponse)
   └── Identificar parâmetros de rota

3. Ler Models relacionados
   └── Mapear campos e tipos
   └── Identificar relacionamentos

4. Ler Enums
   └── Mapear valores permitidos
```

### 2. Estrutura OpenAPI

```yaml
openapi: 3.0.3
info:
  title: Grapo API
  description: API de gestão de locação de ativos
  version: 1.0.0
  contact:
    email: suporte@grapo.com

servers:
  - url: https://api.grapo.com
    description: Produção
  - url: https://staging-api.grapo.com
    description: Staging
  - url: http://localhost:8000
    description: Desenvolvimento

tags:
  - name: Auth
    description: Autenticação e autorização
  - name: Pessoas
    description: Gestão de locadores e locatários
  - name: Contratos
    description: Gestão de contratos de locação

paths:
  /api/auth/login:
    post:
      tags: [Auth]
      summary: Autenticar usuário
      ...

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    Pessoa:
      type: object
      properties:
        id:
          type: string
          format: uuid
        ...
```

## Mapeamento de Tipos

### PHP → OpenAPI

| PHP | OpenAPI |
|-----|---------|
| `string` | `type: string` |
| `int` | `type: integer` |
| `float` | `type: number` |
| `bool` | `type: boolean` |
| `array` | `type: array` |
| `DateTime` | `type: string, format: date-time` |
| `?string` | `type: string, nullable: true` |
| `uuid` | `type: string, format: uuid` |
| `email` | `type: string, format: email` |

### Laravel Validation → OpenAPI

| Validation | OpenAPI |
|------------|---------|
| `required` | Adiciona ao `required: []` |
| `string` | `type: string` |
| `email` | `format: email` |
| `uuid` | `format: uuid` |
| `date` | `format: date` |
| `max:255` | `maxLength: 255` |
| `min:1` | `minimum: 1` |
| `in:a,b,c` | `enum: [a, b, c]` |
| `nullable` | `nullable: true` |

## Template de Endpoint

```yaml
/api/pessoas:
  get:
    tags:
      - Pessoas
    summary: Listar pessoas
    description: Retorna lista paginada de pessoas do locador
    operationId: listPessoas
    security:
      - bearerAuth: []
    parameters:
      - name: tipo
        in: query
        description: Filtrar por tipo de pessoa
        schema:
          type: string
          enum: [locador, locatario, responsavel_fin, responsavel_adm]
      - name: page
        in: query
        description: Número da página
        schema:
          type: integer
          default: 1
      - name: per_page
        in: query
        description: Itens por página
        schema:
          type: integer
          default: 15
    responses:
      '200':
        description: Lista de pessoas
        content:
          application/json:
            schema:
              type: object
              properties:
                data:
                  type: array
                  items:
                    $ref: '#/components/schemas/Pessoa'
                meta:
                  $ref: '#/components/schemas/PaginationMeta'
      '401':
        $ref: '#/components/responses/Unauthorized'
```

## Template de Schema

```yaml
components:
  schemas:
    Pessoa:
      type: object
      required:
        - id
        - tipo
        - nome
      properties:
        id:
          type: string
          format: uuid
          readOnly: true
          example: "550e8400-e29b-41d4-a716-446655440000"
        tipo:
          type: string
          enum: [locador, locatario, responsavel_fin, responsavel_adm]
          example: "locatario"
        nome:
          type: string
          maxLength: 255
          example: "João da Silva"
        email:
          type: string
          format: email
          nullable: true
          example: "joao@email.com"
        telefone:
          type: string
          nullable: true
          example: "(11) 99999-9999"
        ativo:
          type: boolean
          default: true
        created_at:
          type: string
          format: date-time
          readOnly: true
        updated_at:
          type: string
          format: date-time
          readOnly: true

    PaginationMeta:
      type: object
      properties:
        current_page:
          type: integer
        last_page:
          type: integer
        per_page:
          type: integer
        total:
          type: integer

    Error:
      type: object
      properties:
        message:
          type: string
        errors:
          type: object
          additionalProperties:
            type: array
            items:
              type: string

  responses:
    Unauthorized:
      description: Não autenticado
      content:
        application/json:
          schema:
            type: object
            properties:
              message:
                type: string
                example: "Unauthenticated."

    NotFound:
      description: Recurso não encontrado
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'

    ValidationError:
      description: Erro de validação
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'
```

## Instruções para o Agente

1. **Ler** `routes/api.php` e identificar todas as rotas
2. **Para cada rota:**
   - Identificar método HTTP (GET, POST, PUT, DELETE)
   - Identificar controller associado
   - Ler controller e extrair:
     - Validações do Request
     - Parâmetros de rota
     - Estrutura do Response
3. **Ler** Models em `app/Models/` para gerar schemas
4. **Ler** Enums em `app/Enums/` para mapear valores permitidos
5. **Gerar** arquivo YAML seguindo OpenAPI 3.0
6. **Salvar** em `docs/openapi.yaml` (ou path especificado)
7. **Validar** estrutura do YAML gerado

## Arquivo de Saída

Local padrão: `docs/openapi.yaml`

O arquivo gerado pode ser usado com:
- **Swagger UI** - Documentação interativa
- **Postman** - Importar coleção
- **Insomnia** - Importar workspace
- **Code generators** - Gerar SDKs

## Exemplo de Saída Completa

```yaml
openapi: 3.0.3
info:
  title: Grapo API
  version: 1.0.0

servers:
  - url: http://localhost:8000

security:
  - bearerAuth: []

paths:
  /api/auth/login:
    post:
      tags: [Auth]
      summary: Login
      security: []  # Rota pública
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [email, password]
              properties:
                email:
                  type: string
                  format: email
                password:
                  type: string
                  format: password
      responses:
        '200':
          description: Login bem sucedido
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: object
                    properties:
                      token:
                        type: string
                      user:
                        $ref: '#/components/schemas/User'
        '401':
          description: Credenciais inválidas

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer

  schemas:
    User:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
        email:
          type: string
          format: email
```
