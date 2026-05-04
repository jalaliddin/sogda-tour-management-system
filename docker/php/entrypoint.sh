#!/bin/sh
set -e

cd /var/www/backend

# Fix storage permissions (needed when volume is first mounted)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create storage symlink for public uploads
php artisan storage:link --force 2>/dev/null || true

echo "==> Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
  sleep 2
done
echo "==> MySQL ready."

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting PHP-FPM..."
exec "$@"
