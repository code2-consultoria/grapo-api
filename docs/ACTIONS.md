# Action Driven Development no Laravel

O padrão começou a ser utilizado pelo Fortify e ganhou força dentro da comunidade do Laravel. Padrão consiste em ter ações específicar (como CreateUser) separadas e prontas para reúso, reforçando a ideia de responsabilidade única do SOLID.

## Exemplo

Um exemplo que sempre uso é o de uma importação de vendas realizadas em player como a VTEX. Digamos que meu negócio está baseado em vendas em uma loja na vtex e nela tenho que sincronizar meu estoque, sendo que também tenho vendas físicas, no balcão, então cada venda feita no balcão (no meu frente de loja) deve ser sincronizada na VTEX e vice-versa.

Num primeiro momento eu tenho que fazer um loop para buscar todas as vendas de um determinado período para evitar respostas 429. Então ficaria algo assim:

1 - request http para buscar as vendas
2 - loop sobre o resultado
3 - instanciar o model e criar novo registro
4 - lidar com regras de negócio
5 - disparar eventos relativos à criação da venda (o que atualizaria o estoque)

Para disparar a sincronização da venda do meu ambiente local para a VTEX eu teria que fazer algo semelhante, porém com um único item:

1 - instanciar o model e criar novo registro
2 - lidar com regras de negócio
3 - disparar eventos relativos à criação da venda (o que atualizaria o estoque)

Com uma action CriarRegistroVenda ficaria algo assim para a sincronização do estoque local com a VTEX:

1 - request http para buscar as vendas
2 - loop sobre o resultado
3 - instanciar CriarRegistroVenda e preencher os atributos

No item 3 estaríamos encapsulando a complexidade dos itens 3, 4 e 5 do cenário inicial de sincronização das vendas da VTEX com o nosso estoque local. Mas como ficaria essa classe? Vejamos a seguir:

### Código de uma action para o cenário mostrado acima

Teríamos o cenário inicial assim:

<?php
// namespace e uses omitidos para este exemplo

$vendas = Http::get('<endpoint vtex>'); //configuração com informações sobre autenticação suprimidos

foreach($vendas as $venda) {
    \DB::beginTransaction();
    
    try {
        $v = VendaModel::make([
            //informações da venda
        ]);
        
        $user = User::where('cpf', $v->user->cpf);
        
        $v->user()->associate($user);
        $v->save();
        
        foreach($venda->itens as $item) {
            $p = Produto::find($item->produtoId);
            
            $i = Item::make([
                // dados do item
            ]);
            $i->venda()->associate($v);
            $i->save();
        }
        
        // Regras de negócio que precisassem ser observadas
        
        \DB::commit();
    } catch (\Exception $e) {
        \DB::rollback();
        info('...');
    }
}

Teríamos o código reduzido a isto depois de criar a action CriarRegistroVenda

$vendas = Http::get('<endpoint vtex>'); //configuração com informações sobre autenticação suprimidos

foreach($vendas as $venda) {
    $action = new CriarRegistroVenda($venda);
    unset($action);
}

Onde a CriarRegistroVenda ficaria assim:

<?php
// namespace e uses otimidos para este exemplo

class CriarRegistroVenda
{
    public function __construct(public VendaModel as $venda) {
        // quaisquer outras atribuições que precisasse fazer
    }
    
    public function handle(): <tipo de retorno necessário>
    {
        \DB::beginTransaction();
        
        try {
        $v = VendaModel::make([
            //informações da venda vinda de $this->venda
        ]);
        
        $user = User::where('cpf', $v->user->cpf);
        
        $v->user()->associate($user);
        $v->save();
        
        foreach($this->venda->itens as $item) {
            $p = Produto::find($item->produtoId);
            
            $i = Item::make([
                // dados do item
            ]);
            $i->venda()->associate($v);
            $i->save();
            unset($i, $p);
        }
        
        // Regras de negócio que precisassem ser observadas
        event(new VendaCriada);
        unset($v);
        
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            info('...');
        }
    }
}
