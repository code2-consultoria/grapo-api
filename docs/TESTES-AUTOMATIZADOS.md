# TESTES AUTOMATIZADOS

Prefira o uso do pest ao clássico phpunit.

## Testes com CSRF

Quando testes a endpoints que exijam o uso de checkagem de CSRF, faça o seguinte:

No início do seu teste inicie a sessão com Session::start(), depois, ao montar o payload da requisição http a esse endpoint, adicione o campo '_token' => Session::token() ao payload.
