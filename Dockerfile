# syntax=docker/dockerfile:1.6

FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.2-fpm-alpine AS php_base
WORKDIR /var/www/html

RUN set -eux; \
    apk add --no-cache \
        git \
        curl \
        libzip \
        libzip-dev \
        oniguruma-dev; \
    docker-php-ext-install bcmath pdo_mysql zip; \
    docker-php-ext-enable opcache; \
    apk del --no-cache libzip-dev oniguruma-dev

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

FROM php_base AS builder

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    LOG_CHANNEL=stderr \
    CACHE_DRIVER=array \
    SESSION_DRIVER=array \
    QUEUE_CONNECTION=sync

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --prefer-dist --no-progress --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && find storage -type d -exec chmod 775 {} \; \
    && find storage -type f -exec chmod 664 {} \; \
    && chmod -R ug+rwx bootstrap/cache

FROM php_base AS production

ENV APP_ENV=production \
    APP_DEBUG=false

COPY --from=builder /var/www/html /var/www/html

EXPOSE 9000
CMD ["php-fpm", "-F"]
