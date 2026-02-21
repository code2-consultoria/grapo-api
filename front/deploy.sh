#!/bin/bash
set -e

# Script de deploy do Grapo Front
# Uso: ./deploy.sh
#
# Requisitos:
#   - Arquivo .github_token com o token de acesso ao GitHub Container Registry
#   - Arquivo docker-compose.github.yml no mesmo diretorio
#
# Para deploy manual, edite o arquivo .github_token com um PAT valido

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TOKEN_FILE="$SCRIPT_DIR/.github_token"
COMPOSE_FILE="$SCRIPT_DIR/docker-compose.github.yml"
CONTAINER_NAME="grapo-front"
REGISTRY="ghcr.io"
GITHUB_USER="${GITHUB_USER:-evaldobarbosa}"

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

# Verifica se o arquivo de token existe
if [ ! -f "$TOKEN_FILE" ]; then
    log_error "Arquivo de token nao encontrado: $TOKEN_FILE"
    log_info "Crie o arquivo com: echo 'seu_token' > $TOKEN_FILE"
    exit 1
fi

# Verifica se o docker-compose existe
if [ ! -f "$COMPOSE_FILE" ]; then
    log_error "Arquivo docker-compose nao encontrado: $COMPOSE_FILE"
    exit 1
fi

# Le o token do arquivo
GITHUB_TOKEN=$(cat "$TOKEN_FILE" | tr -d '\n\r')

if [ -z "$GITHUB_TOKEN" ]; then
    log_error "Token vazio no arquivo $TOKEN_FILE"
    exit 1
fi

log_info "Iniciando deploy..."

# Login no GitHub Container Registry
log_info "Fazendo login no $REGISTRY..."
echo "$GITHUB_TOKEN" | docker login "$REGISTRY" -u "$GITHUB_USER" --password-stdin

# Pull da nova imagem
log_info "Baixando nova imagem..."
docker compose -f "$COMPOSE_FILE" pull

# Deploy com force-recreate
log_info "Recriando container..."
docker compose -f "$COMPOSE_FILE" up -d --force-recreate

# Healthcheck
log_info "Aguardando container iniciar..."
sleep 5

CONTAINER_IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$CONTAINER_NAME" 2>/dev/null || echo "")

if [ -z "$CONTAINER_IP" ]; then
    log_error "Nao foi possivel obter IP do container $CONTAINER_NAME"
    docker ps -a | grep "$CONTAINER_NAME"
    exit 1
fi

log_info "Container IP: $CONTAINER_IP"

if curl -sf "http://$CONTAINER_IP:80/" > /dev/null; then
    log_info "Healthcheck OK - Container saudavel!"
else
    log_error "Healthcheck falhou!"
    docker logs "$CONTAINER_NAME" --tail 20
    exit 1
fi

# Limpa imagens antigas
log_info "Limpando imagens antigas..."
docker image prune -f

log_info "Deploy concluido com sucesso!"
