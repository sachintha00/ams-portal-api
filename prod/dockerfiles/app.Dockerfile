ARG USERNAME
ARG RELEASE_VERSION

FROM ghcr.io/${USERNAME}/composer_runner:${RELEASE_VERSION} AS composer_runner
FROM ghcr.io/${USERNAME}/npm_runner:${RELEASE_VERSION} AS npm_runner
FROM php:8.1.0-fpm-alpine3.15 AS php_builder

WORKDIR /var/www/html/

RUN apk --update add \
    postgresql-dev \
    && apk del --no-cache \
    && rm -rf /tmp/*
RUN docker-php-ext-install pdo_pgsql


COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY ../src/ .
COPY --from=npm_runner /app .
COPY --from=composer_runner /app .

RUN chown -R www-data:www-data . \
    && chmod -R 775 .
