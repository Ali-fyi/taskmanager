# syntax=docker/dockerfile:1

# ============================================================
# Stage 1 — PHP dependencies
# ============================================================
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ============================================================
# Stage 2 — Frontend assets
# ============================================================
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./

RUN npm run build

# ============================================================
# Stage 3 — Production application
# ============================================================
FROM php:8.4-fpm-bookworm

WORKDIR /var/www/html

# System dependencies + PostgreSQL PHP extension
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        bcmath \
        opcache \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Production PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# OPcache configuration
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# Copy Composer binary
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy Laravel application
COPY . .

# Copy production PHP dependencies
COPY --from=composer /app/vendor ./vendor

# Laravel production caches
RUN php artisan config:cache \
    && php artisan view:cache

# Copy compiled Vite assets
COPY --from=frontend /app/public/build ./public/build

# Laravel runtime directories
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R ug+rwx \
        storage \
        bootstrap/cache

# Nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Render provides PORT at runtime.
EXPOSE 10000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]