FROM php:8.1.0-fpm-alpine3.15

WORKDIR /app

COPY ../src/composer* .
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN composer install --no-dev --ignore-platform-reqs --no-scripts

COPY ../src/ .

RUN composer dump-autoload