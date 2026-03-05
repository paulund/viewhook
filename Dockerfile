# Stage 1: Build — installs PHP + Node deps and compiles frontend assets
FROM php:8.4-cli AS builder
WORKDIR /app
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y git zip unzip libsqlite3-dev curl \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_sqlite
# Install Node 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*
# Install PHP dependencies
COPY composer*.json ./
RUN composer install --no-dev --no-autoloader --no-scripts --prefer-dist
# Install Node dependencies
COPY package*.json .npmrc ./
RUN npm ci
# Copy full source and build
COPY . .
RUN composer dump-autoload --optimize --no-dev
# Create storage dirs (gitignored) and a minimal .env so artisan can bootstrap during npm build
RUN mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions bootstrap/cache \
    && echo "APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=" > .env
# VITE_REVERB_HOST is intentionally empty so the client uses window.location.hostname at runtime.
# VITE_REVERB_APP_KEY must match the REVERB_APP_KEY env var passed to the container.
ARG VITE_REVERB_APP_KEY=viewhook
ARG VITE_REVERB_PORT=8080
ARG VITE_REVERB_SCHEME=http
RUN npm run build && rm -f .env

# Stage 2: Production image
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    nginx supervisor \
    libsqlite3-dev libzip-dev libicu-dev sqlite3 \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_sqlite opcache pcntl zip intl

RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.validate_timestamps=0" > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

COPY --from=builder --chown=www-data:www-data /app .

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
