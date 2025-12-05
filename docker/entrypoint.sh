#!/bin/sh
set -e

# Link storage if not exists
if [ ! -L /var/www/public/storage ]; then
    php artisan storage:link
fi

# Cache configurations for production speed
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations (optional: be careful in production clusters)
# php artisan migrate --force

# Start PHP-FPM
echo "Starting Raydoc..."
php-fpm