#!/bin/sh
set -e

echo "Inicializando..."

# Criar diretórios se não existirem
mkdir -p /var/www/storage/framework/{sessions,views,cache}
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# Garantir permissões corretas (pode falhar em volumes existentes, ignoramos)
chmod -R 777 /var/www/storage /var/www/bootstrap 2>/dev/null || true

echo "Cache de configuração..."
php artisan config:clear 2>/dev/null || true
php artisan config:cache 2>/dev/null || true

echo "Pronto"

exec "$@"
