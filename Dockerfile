# --------------------------------------
# Stage 1: Build Frontend Assets (Node)
# --------------------------------------
FROM node:20-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# --------------------------------------
# Stage 2: Setup Application (PHP)
# --------------------------------------
FROM php:8.2-fpm

# 1. Install System Dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libicu-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl

# 2. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Set Working Directory
WORKDIR /var/www

# 4. Copy Application Code
COPY . .

# 5. Copy Frontend Build from Stage 1
COPY --from=frontend /app/public/build /var/www/public/build

# 6. Install PHP Dependencies (Optimized)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 7. Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# 8. Entrypoint Script (Run migrations, cache, etc.)
COPY docker/entrypoint.sh /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

EXPOSE 9000
ENTRYPOINT ["start-container"]