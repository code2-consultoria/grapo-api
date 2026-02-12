# Modelo de Dados - Grapo

> **Versao:** 1.1.0 (M1 - MVP Contratos)
> **Ultima atualizacao:** 2025-02-11
> **Milestone:** M1

---

## Diagrama ER

```mermaid
erDiagram
    users ||--|| vinculo_times : possui
    vinculo_times }o--|| pessoas : vincula_locador
    pessoas ||--o{ documentos : possui
    pessoas ||--o{ contratos : locador
    pessoas ||--o{ contratos : locatario
    pessoas ||--o{ tipos_ativos : possui
    pessoas ||--o{ lotes : possui
    pessoas ||--o{ assinaturas : possui
    contratos ||--|{ contrato_itens : contem
    contrato_itens ||--|{ alocacoes_lotes : alocado_de
    tipos_ativos ||--o{ lotes : categoriza
    lotes ||--o{ alocacoes_lotes : fornece
    planos ||--o{ assinaturas : oferece

    users {
        uuid id
        string name
        string email
        string password
        boolean ativo
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    vinculo_times {
        uuid id
        uuid user_id
        uuid locador_id
        timestamp created_at
        timestamp updated_at
    }

    pessoas {
        uuid id
        string tipo
        string nome
        string email
        string telefone
        string endereco
        boolean ativo
        timestamp created_at
        timestamp updated_at
    }

    documentos {
        uuid id
        uuid pessoa_id
        string tipo
        string numero
        timestamp created_at
        timestamp updated_at
    }

    planos {
        uuid id
        string nome
        integer duracao_meses
        decimal valor
        boolean ativo
        timestamp created_at
        timestamp updated_at
    }

    assinaturas {
        uuid id
        uuid locador_id
        uuid plano_id
        date data_inicio
        date data_termino
        string status
        timestamp created_at
        timestamp updated_at
    }

    tipos_ativos {
        uuid id
        uuid locador_id
        string nome
        string descricao
        string unidade_medida
        decimal valor_diaria_sugerido
        timestamp created_at
        timestamp updated_at
    }

    lotes {
        uuid id
        uuid locador_id
        uuid tipo_ativo_id
        string codigo
        integer quantidade_total
        integer quantidade_disponivel
        decimal valor_unitario_diaria
        decimal custo_aquisicao
        date data_aquisicao
        string status
        timestamp created_at
        timestamp updated_at
    }

    contratos {
        uuid id
        uuid locador_id
        uuid locatario_id
        string codigo
        date data_inicio
        date data_termino
        decimal valor_total
        string status
        text observacoes
        timestamp created_at
        timestamp updated_at
    }

    contrato_itens {
        uuid id
        uuid contrato_id
        uuid tipo_ativo_id
        integer quantidade
        decimal valor_unitario_diaria
        decimal valor_total_item
        timestamp created_at
        timestamp updated_at
    }

    alocacoes_lotes {
        uuid id
        uuid contrato_item_id
        uuid lote_id
        integer quantidade_alocada
        timestamp created_at
        timestamp updated_at
    }

```

---

## Diagrama de Classes - Pessoa e Documento

```mermaid
classDiagram
    Pessoa <|-- Locador
    Pessoa <|-- Locatario
    Pessoa "1" --> "*" Documento
    Documento <|-- CPF
    Documento <|-- CNPJ
    Documento <|-- RG
    Documento <|-- InscricaoMunicipal
    Documento <|-- InscricaoEstadual
    Documento <|-- Passaporte
    Documento <|-- CadUnico
    Documento <|-- CNH
    ValidaDocumento <|.. CPF
    ValidaDocumento <|.. CNPJ
    ValidaDocumento <|.. RG
    FormataDocumento <|.. CPF
    FormataDocumento <|.. CNPJ
    FormataDocumento <|.. RG
    DocumentoFactory ..> Documento

    class Pessoa {
        +uuid id
        +string tipo
        +string nome
        +string email
        +string telefone
        +tipoPessoa() string
    }

    class Locador {
        +scopeLocador()
    }

    class Locatario {
        +scopeLocatario()
    }

    class Documento {
        +uuid id
        +uuid pessoa_id
        +string tipo
        +string numero
    }

    class ValidaDocumento {
        <<interface>>
        +validate(string numero) bool
    }

    class FormataDocumento {
        <<interface>>
        +formatar(string numero) string
        +desformatar(string numero) string
    }

    class DocumentoFactory {
        +criar(string tipo, string numero) Documento
    }

    class CPF {
        +validate(string numero) bool
        +formatar(string numero) string
    }

    class CNPJ {
        +validate(string numero) bool
        +formatar(string numero) string
    }
```

---

## Descricao das Entidades

### users
Quem acessa o sistema (credenciais de login).

### vinculo_times
Vincula um User a um Locador. Um User so pode estar vinculado a um Locador.

### pessoas
Entidade unificada para Locador e Locatario. O campo `tipo` define o papel:
- **locador**: Quem aluga equipamentos (empresa de locacao)
- **locatario**: Quem aluga equipamentos do locador (cliente)
- **responsavel_fin**: Responsavel financeiro
- **responsavel_adm**: Responsavel administrativo

**Accessor `tipo_pessoa`**: Retorna PF ou PJ baseado nos documentos:
- **PJ**: CNPJ, Inscricao Municipal, Inscricao Estadual
- **PF**: CPF, RG, CadUnico, Passaporte, CNH

### documentos
Documentos de uma pessoa. Cada tipo tem classe propria com validacao e formatacao.

Tipos suportados:
- cpf
- cnpj
- rg
- cnh
- passaporte
- inscricao_municipal
- inscricao_estadual
- cadunico

### planos
Planos de assinatura da Grapo:
- Trimestral: R$ 75,00
- Semestral: R$ 140,00
- Anual: R$ 250,00

### assinaturas
Vinculo entre locador e plano. Controla o periodo de acesso a plataforma.

### tipos_ativos
Categorizacao dos equipamentos disponiveis para locacao. Ex: "Placa de EVA", "Betoneira 400L".

Isolado por `locador_id`.

### lotes
Agrupamento fisico de ativos do mesmo tipo. O locador cadastra lotes e o sistema controla a disponibilidade.

Isolado por `locador_id`.

### contratos
Acordo formal entre locador e locatario. Contem periodo, valor e status.

**Regra:** Apos ativo, so pode ser alterado via aditivo (funcionalidade futura - ver M2).

### contrato_itens
Itens solicitados pelo locatario no contrato.

### alocacoes_lotes
Tabela interna de mapeamento automatico usando **FIFO** (lote mais antigo primeiro).

**Esta informacao nao e exibida ao usuario.**

---

## Isolamento de Dados

O isolamento e feito por `locador_id`:

1. User faz login
2. Sistema identifica o Locador via `vinculo_times`
3. Todas as queries filtram por `locador_id`

Entidades isoladas:
- tipos_ativos
- lotes
- contratos
- assinaturas

Entidades globais:
- planos
- users

---

## Interfaces CQRS

```mermaid
classDiagram
    class Query {
        <<interface>>
        +execute() mixed
    }

    class Command {
        <<interface>>
        +execute() mixed
    }

    Query <|.. ListarContratos
    Query <|.. BuscarContrato
    Command <|.. CriarContrato
    Command <|.. AtivarContrato
    Command <|.. AlocarLotes
```

Todas as Actions implementam `Query` (leitura) ou `Command` (escrita).

---

## Historico de Versoes

| Versao | Milestone | Alteracoes |
|--------|-----------|------------|
| 1.0.0 | M1 | Modelo inicial com tenants/locatarios separados |
| 1.1.0 | M1 | Unificacao em pessoas, documentos, vinculo_times, CQRS |

---

## Proximas Alteracoes Planejadas

### M2 - Contratos Completo
Novas entidades:
- `contrato_aditivos`
- `contrato_aditivo_itens`

Ver proposta: `docs/analises/features/contrato-aditivos-proposta.md`

---

## Referencias

- Funcionalidade Contratos: `docs/analises/features/contratos.md`
- Proposta Aditivos: `docs/analises/features/contrato-aditivos-proposta.md`
- Milestone atual: `docs/MILESTONES.md`
- Versao anterior: `docs/analises/arquivo/modelo-dados.m1-v1.md`
