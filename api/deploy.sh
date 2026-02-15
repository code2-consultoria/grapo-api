#!/bin/bash
# Deploy script para Grapo API
# Uso: ./deploy.sh [tag]
# Rollback: ./deploy.sh --rollback

set -e

# Le DOCKER_IMAGE do .env ou usa default
source .env 2>/dev/null || true
DOCKER_IMAGE="${DOCKER_IMAGE:-ghcr.io/code2-consultoria/grapo-api}"
DOCKER_TAG="${1:-production}"
COMPOSE_FILE="docker-compose.github.yml"
HISTORY_FILE=".deploy-history"
GH_TOKEN=$(cat .github_token 2>/dev/null || echo "")
export COMPOSE_PROJECT_NAME=grapo

# Helper para executar comandos no container via compose
compose_exec() {
    docker compose -f "$COMPOSE_FILE" exec -T "$@"
}

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Funcao de rollback
rollback() {
    if [ ! -f "$HISTORY_FILE" ]; then
        log_error "Nenhum histórico de deploy encontrado"
        exit 1
    fi

    PREVIOUS_TAG=$(tail -2 "$HISTORY_FILE" | head -1)
    if [ -z "$PREVIOUS_TAG" ]; then
        log_error "Nenhuma versão anterior disponível"
        exit 1
    fi

    log_warn "=== Rollback para $PREVIOUS_TAG ==="
    DOCKER_TAG="$PREVIOUS_TAG"
}

# Verifica se é rollback
if [ "$1" = "--rollback" ] || [ "$1" = "-r" ]; then
    rollback
fi

echo "=========================================="
echo "       Deploy Grapo API"
echo "=========================================="
log_info "Tag: $DOCKER_TAG"
log_info "Compose: $COMPOSE_FILE"

# Login no GitHub Container Registry
if [ -n "$GH_TOKEN" ]; then
    log_info "Fazendo login no GitHub Container Registry..."
    echo "$GH_TOKEN" | docker login ghcr.io -u github --password-stdin
fi

# Salva tag atual antes do deploy (para rollback)
CURRENT_TAG=$(docker compose -f "$COMPOSE_FILE" images api --format '{{.Tag}}' 2>/dev/null || echo "")
if [ -n "$CURRENT_TAG" ] && [ "$CURRENT_TAG" != "$DOCKER_TAG" ]; then
    echo "$CURRENT_TAG" >> "$HISTORY_FILE"
    # Mantém apenas últimas 10 versões
    tail -10 "$HISTORY_FILE" > "$HISTORY_FILE.tmp" && mv "$HISTORY_FILE.tmp" "$HISTORY_FILE"
fi

# Pull da imagem
log_info "Baixando imagem..."
docker pull "$DOCKER_IMAGE:$DOCKER_TAG"

# Pre-flight check: testa a imagem antes de derrubar a atual
log_info "Testando nova imagem (pre-flight check)..."
docker rm -f grapo_api_test 2>/dev/null || true

# Pre-flight usa configuração isolada (sem dependências externas)
# Remove .env interno para que as env vars do container tenham precedência
# Usa /home/start.sh (nginx+php-fpm) em vez de supervisord (que inclui scheduler com dependência de banco)
docker run -d --name grapo_api_test \
    -e FRAMEWORK=laravel \
    -e OPCACHE_MODE=normal \
    -e APP_ENV=local \
    -e APP_DEBUG=false \
    -e APP_KEY=base64:dGVzdGtleWZvcnByZWZsaWdodGNoZWNr \
    -e CACHE_STORE=array \
    -e SESSION_DRIVER=array \
    -e QUEUE_CONNECTION=sync \
    -e DB_CONNECTION=sqlite \
    -e DB_DATABASE=:memory: \
    -e LOG_CHANNEL=stderr \
    -e BROADCAST_CONNECTION=log \
    -e REDIS_HOST=localhost \
    "$DOCKER_IMAGE:$DOCKER_TAG" \
    sh -c "rm -f /var/www/app/.env && php-fpm --daemonize && nginx -g 'daemon off;'"

# Aguarda container de teste iniciar (usa /up que não tem dependências)
PREFLIGHT_TIMEOUT=30
PREFLIGHT_INTERVAL=5
ELAPSED=0
PREFLIGHT_OK=false

while [ $ELAPSED -lt $PREFLIGHT_TIMEOUT ]; do
    sleep $PREFLIGHT_INTERVAL
    ELAPSED=$((ELAPSED + PREFLIGHT_INTERVAL))

    # Verifica se o container ainda está rodando
    if ! docker inspect --format='{{.State.Running}}' grapo_api_test 2>/dev/null | grep -q true; then
        log_error "Container de teste parou inesperadamente. Logs:"
        docker logs grapo_api_test --tail 100 2>/dev/null || true
        docker rm -f grapo_api_test 2>/dev/null || true
        log_error "Deploy abortado."
        exit 1
    fi

    if docker exec grapo_api_test wget -q --spider http://localhost:8080/up 2>/dev/null; then
        PREFLIGHT_OK=true
        break
    fi
    log_info "Aguardando pre-flight... ($ELAPSED/$PREFLIGHT_TIMEOUT s)"
done

if [ "$PREFLIGHT_OK" = false ]; then
    log_error "Pre-flight check falhou! Logs do container de teste:"
    docker logs grapo_api_test --tail 100 2>/dev/null || true
    docker rm -f grapo_api_test 2>/dev/null || true
    log_error "Deploy abortado."
    exit 1
fi

# Remove container de teste
docker rm -f grapo_api_test 2>/dev/null || true

log_info "Pre-flight OK! Imagem validada."

# Stop containers atuais
log_info "Parando containers atuais..."
docker compose -f "$COMPOSE_FILE" down || true

# Start novos containers
log_info "Iniciando novos containers..."
DOCKER_IMAGE="$DOCKER_IMAGE" \
DOCKER_TAG="$DOCKER_TAG" \
docker compose -f "$COMPOSE_FILE" up -d

# Aguarda containers iniciarem
log_info "Aguardando containers iniciarem..."
sleep 15

# Roda migrations
log_info "Executando migrations..."
compose_exec api php artisan migrate --force || log_warn "Migrations falharam ou não há novas migrations"

# Cria symlink para storage público
log_info "Criando symlink do storage..."
compose_exec api php artisan storage:link || true

# Limpa cache e otimiza
log_info "Otimizando aplicação (API)..."
compose_exec api php artisan config:cache || true
compose_exec api php artisan route:cache || true
compose_exec api php artisan view:cache || true

log_info "Otimizando aplicação (Queue Worker)..."
compose_exec queue php artisan config:cache || true

# Health check com retry (via docker exec)
log_info "Verificando saúde da aplicação..."
HEALTH_CHECK_TIMEOUT=60
HEALTH_CHECK_INTERVAL=5
ELAPSED=0

while [ $ELAPSED -lt $HEALTH_CHECK_TIMEOUT ]; do
    if compose_exec api wget -q --spider http://localhost:8080/api/health 2>/dev/null; then
        break
    fi
    sleep $HEALTH_CHECK_INTERVAL
    ELAPSED=$((ELAPSED + HEALTH_CHECK_INTERVAL))
    log_info "Aguardando API... ($ELAPSED/$HEALTH_CHECK_TIMEOUT s)"
done

if compose_exec api wget -q --spider http://localhost:8080/api/health 2>/dev/null; then
    log_info "Health check passou!"
    echo "$DOCKER_TAG" >> "$HISTORY_FILE"
else
    log_error "Health check falhou - iniciando rollback automático..."
    docker compose -f "$COMPOSE_FILE" logs --tail=50

    # Só faz rollback se a tag anterior for válida (production, staging ou build-*)
    if [ -n "$CURRENT_TAG" ] && [[ "$CURRENT_TAG" =~ ^(production|staging|build-[0-9]+)$ ]]; then
        log_warn "Revertendo para $CURRENT_TAG..."
        DOCKER_TAG="$CURRENT_TAG"
        docker pull "$DOCKER_IMAGE:$DOCKER_TAG"

        DOCKER_IMAGE="$DOCKER_IMAGE" \
        DOCKER_TAG="$DOCKER_TAG" \
        docker compose -f "$COMPOSE_FILE" up -d

        sleep 10

        if compose_exec api wget -q --spider http://localhost:8080/api/health 2>/dev/null; then
            log_info "Rollback bem sucedido"
        else
            log_error "Rollback também falhou!"
        fi
    else
        log_warn "Nenhuma versão anterior válida para rollback (tag: $CURRENT_TAG)"
    fi
    exit 1
fi

echo "=========================================="
log_info "Deploy concluído com sucesso!"
echo "=========================================="
docker compose -f "$COMPOSE_FILE" ps
