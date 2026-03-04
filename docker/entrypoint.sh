#!/bin/bash
set -e

# Ensure /data exists and is writable for SQLite
mkdir -p /data
touch /data/database.sqlite
chown -R www-data:www-data /data

# Ensure storage directories exist
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Run database migrations (can be disabled with RUN_MIGRATIONS=false)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php /var/www/html/artisan migrate --force --no-interaction
fi

# Create storage symlink if it does not already exist
if [ ! -L /var/www/html/public/storage ]; then
    php /var/www/html/artisan storage:link --force
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
