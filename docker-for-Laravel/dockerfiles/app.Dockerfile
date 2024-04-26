FROM php:8.1.0-fpm-alpine3.15

RUN apk --update add \
    shadow \
    sudo \
    npm \
    make \
    postgresql-dev \
    && apk del --no-cache

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers


RUN docker-php-ext-install pdo_pgsql


RUN mkdir -p /var/lib/postgresql/data
RUN chown -R www-data:www-data /var/www/html


COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
