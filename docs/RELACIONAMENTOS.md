# Relacionamentos entre tabelas

Regra primordial: não criar chaves estrangeiras que podem ficar nulas.

Se um relacionamento pode ficar nulo, então é porque aquela propriedade não é daquela tabela. Vejamos o exemplo de Áreas e Subáreas do conhecimento.

Tanto área principal (como ciências da saúde) como subáreas (como medicina, enfermagem, etc) são áreas e as principais estão em uma árvore hierárquica que deve ser apresentada como tal, porém o primeiro nó dessa estrutura, não tem uma área maior e, portanto, não deve ter um campo area_id. Para isso criamos uma tabela area (model Area) e uma tabela subarea que liga as duas áreas (principal e filha) pelos campos area_id e subarea_id respectivamente.

No laravel esse relacionamento entre área e subárea pode ser manipulado por um HasManyThrough.

## Preenchimento de chaves estrangeiras

Não criar chaves estrangeiras em fillable de models. Prefira utilizar o belongsTo e para preencher essa coluna, use o attach() como no exemplo.

Digamos que temos CartItem e Cart, onde Cart é o carrinho, ou seja, a classe principal dessa relação, e CartItem a classe que vai ligar o produto ao carrinho informando quantidade e outras coisas. Para ligar CartItem a Card (e a Product), faríamos assim:

$cardItem = CartItem::make([
    'quantity' => 2,
    'price' => 2.9
]);
$cartItem->product()->associate($product);
$cartItem->cart()->associate($cart);
$card->save();
