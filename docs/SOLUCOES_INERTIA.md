# Soluções para Problema do Inertia

## Problema:
```
Uncaught (in promise) Error: Page not found: ./pages/[NomeDaPagina].vue
```

## Possíveis Causas:

### 1. Cache do Vite/Browser
- O Vite pode estar usando cache antigo
- O browser pode ter cache do JavaScript

### 2. Build não atualizado
- Arquivos novos não estão sendo incluídos no build
- O glob pattern do Vite não está reconhecendo os novos arquivos

### 3. Configuração do Vite
- Problema na configuração do `vite.config.ts`
- Problema no glob pattern em `app.ts`

### 4. Servidor de desenvolvimento
- O servidor de desenvolvimento não está rodando
- Hot reload não está funcionando

## Soluções Propostas:

### Solução 1: Limpar todos os caches
```bash
# No Docker
docker exec [container_name] rm -rf node_modules/.vite
docker exec [container_name] rm -rf public/build
docker exec [container_name] npm run build

# Ou localmente
rm -rf node_modules/.vite
rm -rf public/build
npm run build
```

### Solução 2: Verificar se o servidor está rodando
```bash
# Verificar se o Vite está rodando
docker exec [container_name] npm run dev

# Ou verificar se o build está funcionando
docker exec [container_name] npm run build
```

### Solução 3: Verificar configuração do Vite
- Verificar `vite.config.ts`
- Verificar se o glob pattern está correto

### Solução 4: Usar página existente temporariamente
- Usar uma página que já funciona como teste
- Exemplo: 'Dashboard' (sem pasta específica)

## Solução Temporária Implementada:

1. Criado `TestePage.vue` na raiz
2. Alterado controller para usar `TestePage`
3. Página simples para testar funcionamento básico

## Comandos para Testar:

```bash
# Limpar cache do browser (F12 > Application > Storage > Clear site data)

# Verificar se arquivos estão sendo servidos
curl -I https://[domain]/build/assets/app-[hash].js

# Verificar logs do Vite
docker logs [container_name] | grep vite
```

## Próximos Passos:

1. Testar com `TestePage`
2. Se funcionar, problema é específico do arquivo
3. Se não funcionar, problema é no build/cache
4. Implementar solução definitiva