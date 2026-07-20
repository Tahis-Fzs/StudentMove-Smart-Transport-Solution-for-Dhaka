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

# Keep .env APP_KEY aligned with Render (or generate one if still missing).
php -r '
$envPath = ".env";
$env = is_readable($envPath) ? file_get_contents($envPath) : "";
$renderKey = getenv("APP_KEY") ?: "";
if ($renderKey !== "") {
    $env = preg_replace("/^APP_KEY=.*/m", "APP_KEY=".$renderKey, $env);
    file_put_contents($envPath, $env);
    exit(0);
}
if (!preg_match("/^APP_KEY=base64:/m", $env)) {
    passthru("php artisan key:generate --force");
}
'

# Drop stale bootstrap caches (signed route closures break when APP_KEY changes).
rm -f bootstrap/cache/config.php bootstrap/cache/routes*.php bootstrap/cache/services.php 2>/dev/null || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

if ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "ERROR: APP_KEY is still missing after boot setup."
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
