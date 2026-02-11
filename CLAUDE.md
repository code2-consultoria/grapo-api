# Diretrizes do Projeto para Claude

## Stack Tecnológico
- Frontend (PWA): Vuejs + Javascript + InertiaJS + Tailwind
- Backend: Laravel 12 + Breeze
- Banco: PostgreSQL

## Stack Principal
- Laravel
- Pacotes personalizados
- Invokable Controllers
- Action Driven Development
- Event Driven Development

## Padrões de Código
- Sempre se baseie nos princípios do SOLID
- Sempre leve em conta object calisthenics
- Sempre priorize a quebra de problemas maiores e problemas menores (como uma pipeline)
- Sempre priorize testes automatizados. Toda nova sugestão deve trazer primeiro os testes automatizados (no backend, pelo menos)
- Não coloque campos de chave estrangeira no fillable dos models. Procure usar associate, attach e não a atribuição direta;
- Evite as estruturas if..elseif..else, troque-as por switch;
- Faça melhor uso do namespace: Não use sufixos como Index, Update, Create ou qualquer coisa do tipo para controllers. Por exemplo: um controller Aluno/DesafioIndex deveria ser Aluno/Desafio/Index ou Aluno/Desafio/Lista (em caso de fazer uma lista de desafios);
- Não use sufixos (Controller, Model, Service, Listener, Event, etc) em nome de classes. Se preciso for utilizar um model User em um controller User, faça um alias no model como abaixo:
    <?php
    namespace App\Http\Controllers;
    
    use App\Models\User as UserModel;
    
    class User ...
    {
        public function __invoke(Request $request, UserModel $user)
        {
            //...
        }
    }

## Regras Específicas
- Sempre incluir testes para novas funcionalidades antes de codificar as funcionalidades
- Comentários em português
- Usar conventional commits
- Separar funcionalidades diferentes em diferentes branches
- Testes automatizados devem conter cenários de sucesso e de erro (como validações e regras de negócio gerando exceções)
- Não usar chaves estrangeiras (outra_tabela_id) no fillable de models. Para preencher a chave estrangeira, veja como fazer no link Relacionamentos da Documentação detalhada
- Nunca crie chaves primárias que possam ficar nulas. Avalie se algo vai ser sempre preenchido e se tiver dúvida, pergunte. Se não tiver dúvidas e aquela coluna eventualmente venha a ficar em branco, crie uma tabela auxiliar. Veja o exemplo no item Documentação detalhada, no link Relacionamentos.
- Comandos artisan e composer devem ser rodados no docker. Ex: docker exec -it <nome do container> artisan optimize:clear ou docker exec -it <nome do container> composer renew
- sempre que for rodar migrations, rodar o comando docker exec -it <nome do container> composer renew
- não criar campos enum na base de dados, a aplicação se encarrega de validar os enums

## Documentação Detalhada
- **Sobre a aplicação:** [docs/APP.md](docs/APP.md)
- **Action Driven Development:** [docs/ACTIONS.md](docs/ACTIONS.md)
- **UI/UX:** [docs/UIUX.md](docs/UIUX.md)
- **BACKLOG:** [docs/BACKLOG.md](docs/BACKLOG.md)
- **Relacionamentos:** [docs/RELACIONAMENTOS.md](docs/RELACIONAMENTOS.md)
- **Testes automatizados:** [docs/TESTES-AUTOMATIZADOS.md](docs/TESTES-AUTOMATIZADOS.md)

## Estrutura de Documentação

```
docs/
├── features/              # Documentação de cada feature (usar TEMPLATE.md)
├── mockups/               # Mockups para construção de telas (usar TEMPLATE.md)
├── bugs/                  # Imagens/textos de bugs (sem template, informar no chat)
├── sprints/               # Backlog, decisões e artefatos das sprints (usar TEMPLATE.md)
└── analises/              # Análises técnicas e de negócio
    ├── tecnicas/          # Análises de performance, segurança, arquitetura (usar TEMPLATE.md)
    ├── negocio/           # Análises de regras de negócio (usar TEMPLATE.md)
    └── features/          # Rascunhos em estudo antes de virar feature (usar TEMPLATE.md, aplicar INVEST)
```

## Artefatos de Design/UX

Antes de uma feature ser desenvolvida, deve conter:
- Mockups criados e aprovados
- Formalização da aprovação de cada mockup (registrar no template)
- Critérios de aceitação em Gherkin
- Backlog da sprint atualizado

### Storybook

- Usar Storybook para documentar componentes Vue
- Vincular mockups aos stories correspondentes após implementação
- Documentar estados da UI: default, loading, vazio, erro, sucesso

## Diagramas

- Sempre perguntar ao desenvolvedor antes de criar diagramas (alguns casos são simples demais)
- Diagramas prioritários: Fluxo, Sequência, Atividade
- Diagrama de Classes quando necessário
- Sugerir outros diagramas (Estados, Componentes, etc.) para casos mais complexos

## Interação desenvolvedor e Claude

### Fase de Análise
1. Fazer perguntas antes de criar qualquer código (classes, componentes, views)
2. Interagir para que o desenvolvedor possa descrever, corrigir e opinar
3. Para features em estudo, criar rascunho em `docs/analises/features/` usando INVEST

### Fase de Planejamento
4. Após confirmação do entendimento, criar lista de tarefas para aprovação
5. Perguntar se diagramas são necessários (sugerir tipos apropriados para casos complexos)
6. Após aprovação, criar arquivo `sprint-<numero>.md` em `docs/sprints/`

### Fase de Design
7. Criar/revisar mockups e salvar em `docs/mockups/`
8. Obter aprovação formal dos mockups (registrar no template)
9. Definir critérios de aceitação em Gherkin
10. Mapear componentes Vue identificados (vincular ao Storybook quando implementados)

### Fase de Desenvolvimento
11. Criar testes automatizados e aprovar com o desenvolvedor
12. Desenvolver endpoints da API
13. Rodar testes e corrigir até funcionar
14. Implementar componentes Vue e documentar no Storybook
15. Criar documentação final da feature em `docs/features/`

### Registro de Bugs
- Salvar evidências (imagens/texto) em `docs/bugs/`
- Informar no chat para discussão
