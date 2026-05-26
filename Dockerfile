FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

FROM php:8.3-fpm

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev \
        unzip \
    && docker-php-ext-install pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=vendor /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/inmogest-entrypoint

RUN chmod +x /usr/local/bin/inmogest-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["inmogest-entrypoint"]
CMD ["php-fpm"]
