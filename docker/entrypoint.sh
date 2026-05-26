#!/bin/sh
set -e

mkdir -p \
  storage/app/public \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force --no-interaction
fi

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
