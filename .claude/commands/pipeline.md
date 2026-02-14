# Monitor Pipeline

Monitora a pipeline do GitHub Actions até ela completar.

## Uso

```
/pipeline [intervalo_segundos]
```

- `intervalo_segundos`: Intervalo entre verificações (padrão: 30)

## Instruções

Execute o seguinte para monitorar a última pipeline:

```bash
# Verificar última run
gh run list --limit 1

# Monitorar jobs
gh run view <RUN_ID> --json jobs --jq '.jobs[] | "\(.name): \(.status) - \(.conclusion // "running")"'

# Ver logs de erro (se falhou)
gh run view <RUN_ID> --log-failed | tail -50
```

Ou use o script automatizado:

```bash
./scripts/monitor-pipeline.sh 30
```
