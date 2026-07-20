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

# Keep .env APP_KEY in sync with Render's injected APP_KEY (Docker build key differs otherwise).
if [ -n "${APP_KEY:-}" ]; then
  if grep -q '^APP_KEY=' .env 2>/dev/null; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
  else
    echo "APP_KEY=${APP_KEY}" >> .env
  fi
elif ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "Generating APP_KEY (Render env var not set)..."
  php artisan key:generate --force || true
fi

# Drop stale bootstrap caches (signed route closures break when APP_KEY changes).
rm -f bootstrap/cache/config.php bootstrap/cache/routes*.php bootstrap/cache/services.php 2>/dev/null || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

if [ -z "${APP_KEY:-}" ] && ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "WARNING: APP_KEY is missing. Set it in Render env vars or run php artisan key:generate."
fi

if [ ! -f public/build/manifest.json ]; then
  echo "WARNING: Vite manifest missing (public/build/manifest.json). Frontend pages will 500 until assets are built."
fi

# Migrations only — avoid config/route/view cache on Render (env vars + SQLite boot order).
(
  sleep 2
  php artisan migrate --force || true
  php artisan storage:link || true
) &

echo "Starting Apache on ${PORT}..."
exec apache2-foreground
