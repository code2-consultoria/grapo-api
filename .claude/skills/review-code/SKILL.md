---
name: review-code
description: Revisa código aplicando princípios SOLID e Object Calisthenics. Use para garantir qualidade e boas práticas no código.
argument-hint: "[arquivo|diretório|--staged]"
context: fork
agent: general-purpose
allowed-tools: Read, Glob, Grep
---

# Agente: Revisão de Código (SOLID + Object Calisthenics)

Analisa código PHP/Laravel aplicando princípios SOLID e Object Calisthenics para garantir código limpo, manutenível e de alta qualidade.

## Uso

- `/review-code app/Actions/Contrato` - Revisa um diretório
- `/review-code app/Models/User.php` - Revisa um arquivo específico
- `/review-code --staged` - Revisa arquivos staged no git

## Princípios SOLID

### S - Single Responsibility Principle (SRP)
- [ ] Cada classe tem apenas uma razão para mudar?
- [ ] Métodos fazem apenas uma coisa?
- [ ] O nome da classe reflete sua única responsabilidade?

### O - Open/Closed Principle (OCP)
- [ ] Código está aberto para extensão, fechado para modificação?
- [ ] Usa interfaces/abstrações para permitir extensibilidade?
- [ ] Novos comportamentos podem ser adicionados sem alterar código existente?

### L - Liskov Substitution Principle (LSP)
- [ ] Subclasses podem substituir suas classes base sem quebrar o sistema?
- [ ] Contratos são respeitados nas implementações?
- [ ] Não há violações de pré/pós-condições?

### I - Interface Segregation Principle (ISP)
- [ ] Interfaces são pequenas e focadas?
- [ ] Classes não são forçadas a implementar métodos que não usam?
- [ ] Há muitos métodos opcionais/vazios?

### D - Dependency Inversion Principle (DIP)
- [ ] Módulos de alto nível dependem de abstrações?
- [ ] Dependências são injetadas, não instanciadas internamente?
- [ ] Usa-se dependency injection corretamente?

## Object Calisthenics

### 1. Apenas um nível de indentação por método
```php
// ❌ Ruim
public function process($items) {
    foreach ($items as $item) {
        if ($item->isValid()) {
            if ($item->hasStock()) {
                // código...
            }
        }
    }
}

// ✅ Bom
public function process($items) {
    $validItems = $this->filterValid($items);
    $this->processItems($validItems);
}
```

### 2. Não use ELSE
```php
// ❌ Ruim
if ($condition) {
    return $a;
} else {
    return $b;
}

// ✅ Bom (early return)
if ($condition) {
    return $a;
}
return $b;
```

### 3. Encapsule primitivos e strings
```php
// ❌ Ruim
public function setEmail(string $cpf) {}

// ✅ Bom
public function setEmail(Document $cpf) {}
```

Há casos em que só trocar o primitivo por um Value Object não é o bastante, é preciso analisar mais aprofundadamente. Necessita análise dos exemplos abaixo:

E-mail é um contato de uma pessoa, portanto vira Contato. Telefone também é contato.
CPF, CNPJ, Passaporte, RG, CNJ e outros no Brasil são Documentos.

### 4. Coleções de primeira classe
```php
// ❌ Ruim
/** @var array<Item> */
private array $items;

// ✅ Bom
private ItemCollection $items;
```
Tratado como opcional. Severidade baixa.

### 5. Um ponto por linha (Law of Demeter)
```php
// ❌ Ruim
$this->user->getAddress()->getCity()->getName();

// ✅ Bom
$this->user->getCityName();
```
Tratado como opcional. Severidade baixa.

### 6. Não abrevie
```php
// ❌ Ruim
$mgr, $qty, $usr, $btn

// ✅ Bom
$manager, $quantity, $user, $button
```

### 7. Mantenha entidades pequenas
- Classes: máximo ~100 linhas
- Métodos: máximo ~10 linhas
- Pacotes: máximo ~10 classes

### 8. Não mais que dois atributos de instância
```php
// ❌ Ruim
class Order {
    private $id;
    private $items;
    private $customer;
    private $total;
    private $status;
}

// ✅ Bom (extrair value objects)
class Order {
    private OrderId $id;
    private OrderDetails $details;
}
```
Tratado como opcional. Severidade baixa.

### 9. Não use getters/setters
```php
// ❌ Ruim
$order->setStatus('completed');

// ✅ Bom (comportamento, não dados)
$order->complete();
```

## Formato do Relatório

Para cada arquivo analisado, produza:

```markdown
## 📄 [nome-do-arquivo.php]

### Violações Encontradas

| Princípio | Severidade | Descrição | Linha |
|-----------|------------|-----------|-------|
| SRP | 🔴 Alta | Classe com múltiplas responsabilidades | 15-45 |
| OC #2 | 🟡 Média | Uso de else desnecessário | 32 |

### Sugestões de Refatoração

1. **Extrair classe `OrderCalculator`**
   - Mover lógica de cálculo das linhas 15-45
   - Injetar como dependência

2. **Usar early return**
   ```php
   // Antes (linha 32)
   if ($valid) { ... } else { ... }

   // Depois
   if (!$valid) {
       return;
   }
   // código principal
   ```

### Pontos Positivos ✅
- Boa nomenclatura de métodos
- Dependency injection bem aplicado
```

## Níveis de Severidade

- 🔴 **Alta**: Viola princípio fundamental, dificulta manutenção
- 🟡 **Média**: Pode causar problemas futuros, merece atenção
- 🟢 **Baixa**: Melhoria nice-to-have, não urgente

## Instruções para o Agente

1. **Ler** os arquivos especificados em $ARGUMENTS
2. **Analisar** aplicando cada checklist de SOLID e Object Calisthenics
3. **Identificar** violações com linha específica
4. **Sugerir** refatorações concretas com código
5. **Destacar** pontos positivos também
6. **Priorizar** por severidade
7. **Resumir** com score geral (ex: "7/10 - Bom, com melhorias pontuais")
