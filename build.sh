#!/bin/bash
# Build script for Render.com deployment

echo "Starting Laravel build process..."

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Clear caches only — never route:cache on Render (closure routes + APP_KEY mismatch).
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "Build process completed successfully!"
