---
name: deploy
description: Realiza o deploy da aplicação para o ambiente especificado. Use quando precisar publicar alterações em staging ou produção.
argument-hint: "[staging|production]"
disable-model-invocation: true
allowed-tools: Bash(git *), Bash(docker *), Bash(php artisan *)
---

# Skill: Deploy da Aplicação

Realiza o deploy da aplicação Grapo para o ambiente especificado.

## Uso

- `/deploy staging` - Deploy para ambiente de staging
- `/deploy production` - Deploy para produção (requer confirmação)
- `/deploy` - Mostra status atual e opções disponíveis

## Checklist Pré-Deploy

Antes de iniciar o deploy, verifique:

1. [ ] Todos os testes passando (`/test`)
2. [ ] Não há alterações não commitadas (`git status`)
3. [ ] Branch correta (staging → develop, production → main)
4. [ ] Migrations pendentes identificadas

## Processo de Deploy

### 1. Validação
```bash
# Verificar branch atual
git branch --show-current

# Verificar status
git status

# Rodar testes
php artisan test
```

### 2. Deploy para Staging
```bash
# Fazer merge para develop (se necessário)
git checkout develop
git merge feature/branch --no-ff

# Push
git push origin develop

# Acionar deploy (GitHub Actions ou manual)
```

### 3. Deploy para Production
```bash
# ATENÇÃO: Confirmar com usuário antes de prosseguir!

# Fazer merge para main
git checkout main
git merge develop --no-ff

# Criar tag de versão
git tag -a v1.x.x -m "Release v1.x.x"

# Push com tags
git push origin main --tags
```

## Pós-Deploy

1. Verificar health check da aplicação
2. Rodar migrations se necessário: `php artisan migrate --force`
3. Limpar caches: `php artisan optimize:clear`
4. Verificar logs por erros

## Ambientes

| Ambiente | Branch | URL |
|----------|--------|-----|
| Staging | develop | staging.4luga.com |
| Production | main | app.4luga.com |

## Rollback

Se algo der errado:

```bash
# Reverter para versão anterior
git revert HEAD
git push

# Ou restaurar tag específica
git checkout v1.x.x
```

## Importante

- **NUNCA** faça deploy direto para production sem passar por staging
- **SEMPRE** aguarde confirmação do usuário antes de deploy em production
- Mantenha o usuário informado sobre cada etapa do processo
