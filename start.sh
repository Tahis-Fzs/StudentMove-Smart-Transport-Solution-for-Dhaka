#!/bin/bash
set -euo pipefail

cd /var/www/html

# Render (and many PaaS) route traffic to $PORT — Apache must listen there.
PORT="${PORT:-80}"
echo "Configuring Apache for PORT=${PORT}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf || true
if grep -q '<VirtualHost \*:' /etc/apache2/sites-available/000-default.conf; then
  sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

echo "Preparing SQLite database..."
mkdir -p database storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
touch database/database.sqlite
chown -R www-data:www-data database storage bootstrap/cache || true
chmod -R ug+rwx database storage bootstrap/cache || true

echo "Running migrations..."
php artisan migrate --force

echo "Storage link..."
php artisan storage:link || true

echo "Caching config/routes/views..."
# Don't fail boot if cache commands warn
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache on ${PORT}..."
exec apache2-foreground
