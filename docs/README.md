# Documentação do Projeto Grapo

## Estrutura de Pastas

```
docs/
├── features/              # Documentação de features aprovadas e em desenvolvimento
├── mockups/               # Mockups de telas e componentes
├── bugs/                  # Evidências de bugs (imagens, logs)
├── sprints/               # Documentação de sprints (backlog, decisões, artefatos)
└── analises/              # Análises diversas
    ├── tecnicas/          # Performance, segurança, arquitetura
    ├── negocio/           # Regras de negócio, processos
    └── features/          # Rascunhos de features em estudo (pré-aprovação)
```

## Como Usar

### Nova Feature

1. **Estudo inicial:** Criar rascunho em `analises/features/` usando o template
2. **Análise INVEST:** Validar se a feature atende aos critérios
3. **Aprovação:** Mover para `features/` quando aprovada para desenvolvimento
4. **Mockups:** Criar em `mockups/` e registrar aprovação

### Nova Sprint

1. Criar arquivo `sprint-<numero>.md` em `sprints/`
2. Preencher backlog com links para as features
3. Registrar decisões tomadas durante a sprint
4. Listar artefatos produzidos ao final

### Registro de Bug

1. Salvar evidência (screenshot, log) em `bugs/`
2. Nomear arquivo de forma descritiva: `YYYY-MM-DD-descricao-breve.png`
3. Informar no chat para discussão e resolução

### Análises

- **Técnicas:** Investigações de performance, segurança, refatorações
- **Negócio:** Regras de negócio, fluxos de processo, validações com stakeholders
- **Features:** Rascunhos e estudos antes da feature estar pronta

## Templates Disponíveis

| Pasta | Template | Uso |
|-------|----------|-----|
| features/ | TEMPLATE.md | Documentação completa de feature |
| mockups/ | TEMPLATE.md | Registro e aprovação de mockups |
| sprints/ | TEMPLATE.md | Documentação de sprint |
| analises/features/ | TEMPLATE.md | Rascunhos com análise INVEST |
| analises/tecnicas/ | TEMPLATE.md | Análises técnicas |
| analises/negocio/ | TEMPLATE.md | Análises de negócio |

## Convenções

- **Nomes de arquivos:** usar kebab-case (ex: `cadastro-usuario.md`)
- **Datas:** formato YYYY-MM-DD
- **Status:** manter atualizado em cada documento
- **Links:** usar caminhos relativos para facilitar navegação
