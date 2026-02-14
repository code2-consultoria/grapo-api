# Renew Database

Reseta o banco de dados, roda migrations e seeders.

## Uso

```
/renew
```

## Instruções

Execute o comando `composer renew` no container da API:

```bash
docker exec grapo-api composer renew
```

Este comando executa:
1. `migrate:fresh` - Dropa todas as tabelas e roda migrations
2. `db:seed` - Executa os seeders
3. `optimize:clear` - Limpa caches

Dados criados pelo seeder:
- Locador: `locador@example.com`
- Usuário: `test@example.com` / `password`
- Locatário: `locatario@example.com` (vinculado ao locador)
