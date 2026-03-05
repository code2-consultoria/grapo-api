#!/usr/bin/env ash

echo "Executando migrations..."

php artisan migrate --force

echo "Migrations concluidas."
