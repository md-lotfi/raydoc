# --------------------------------------
# Stage 1: Install Composer Dependencies
# --------------------------------------
FROM composer:2 as deps
WORKDIR /app
COPY composer.json composer.lock ./
# Install dependencies so we have the 'vendor' folder
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs \
    --optimize-autoloader \
    --no-scripts

# --------------------------------------
# Stage 2: Build Frontend Assets (Node)
# --------------------------------------
FROM node:20-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

# Copy the rest of the app source code
COPY . .

# ✅ KEY FIX: Copy the 'vendor' directory from Stage 1
# This allows Vite to find '../../vendor/livewire/flux/dist/flux.css'
COPY --from=deps /app/vendor ./vendor

# Now run the build
RUN npm run build

# --------------------------------------
# Stage 3: Setup Application (PHP)
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
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Set Working Directory
WORKDIR /var/www

# 4. Copy Application Code
COPY . .

# 5. Copy PHP Dependencies from Stage 1
COPY --from=deps /app/vendor /var/www/vendor

# 6. Copy Frontend Build from Stage 2
COPY --from=frontend /app/public/build /var/www/public/build

# 7. Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# 8. Entrypoint Script
COPY docker/entrypoint.sh /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

EXPOSE 9000
ENTRYPOINT ["start-container"]