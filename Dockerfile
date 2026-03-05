# Stage 1: Build frontend assets
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY package*.json .npmrc ./
RUN npm ci
COPY . .
# VITE_REVERB_HOST is intentionally empty so the client uses window.location.hostname at runtime.
# VITE_REVERB_APP_KEY must match the REVERB_APP_KEY env var passed to the container.
ARG VITE_REVERB_APP_KEY=viewhook
ARG VITE_REVERB_PORT=8080
ARG VITE_REVERB_SCHEME=http
RUN npm run build

# Stage 2: Install PHP dependencies
FROM php:8.4-cli AS composer-builder
WORKDIR /app
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y git zip unzip libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_sqlite
COPY composer*.json ./
RUN composer install --no-dev --no-autoloader --prefer-dist --optimize-autoloader
COPY . .
COPY --from=node-builder /app/public/build ./public/build
RUN composer dump-autoload --optimize --no-dev

# Stage 3: Production image
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    nginx supervisor \
    libsqlite3-dev libzip-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_sqlite opcache pcntl zip intl

RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.validate_timestamps=0" > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

COPY --from=composer-builder --chown=www-data:www-data /app .

COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN mkdir -p /var/log/supervisor /var/log/nginx

# Production defaults — override via environment variables at runtime
ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite \
    QUEUE_CONNECTION=database \
    CACHE_STORE=database \
    SESSION_DRIVER=cookie \
    REVERB_APP_KEY=viewhook \
    REVERB_APP_SECRET=viewhook-secret \
    REVERB_SERVER_HOST=0.0.0.0 \
    REVERB_SERVER_PORT=6001 \
    REVERB_HOST=localhost \
    REVERB_PORT=8080 \
    REVERB_SCHEME=http

VOLUME ["/data"]
EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
