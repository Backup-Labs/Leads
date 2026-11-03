# ==========================================
# STAGE 1: Frontend Build (Node.js & Vite)
# ==========================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json tsconfig.json vite.config.ts ./
RUN npm ci
COPY resources resources
COPY routes routes
RUN npm run build

# ==========================================
# STAGE 2: PHP Vendor Dependencies (Composer)
# ==========================================
FROM composer:2.8 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ==========================================
# STAGE 3: Production Runtime (PHP-FPM)
# ==========================================
FROM php:8.2-fpm-alpine AS runtime

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    bash \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
        zip

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

# Copy application source
COPY . .

# Copy pre-built vendor and frontend assets from prior stages
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=frontend-builder /app/public/build ./public/build

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
