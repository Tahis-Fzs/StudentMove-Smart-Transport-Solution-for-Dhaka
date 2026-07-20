#!/bin/bash
set -euo pipefail

cd /var/www/html

PORT="${PORT:-80}"
echo "Configuring Apache for PORT=${PORT}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf || true
if grep -q '<VirtualHost \*:' /etc/apache2/sites-available/000-default.conf; then
  sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

mkdir -p database storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
touch database/database.sqlite
chown -R www-data:www-data database storage bootstrap/cache || true
chmod -R ug+rwx database storage bootstrap/cache || true

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Heavy Laravel boot runs after Apache is listening (health check hits /health.html).
(
  sleep 2
  php artisan migrate --force || true
  php artisan storage:link || true
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
) &

echo "Starting Apache on ${PORT}..."
exec apache2-foreground
