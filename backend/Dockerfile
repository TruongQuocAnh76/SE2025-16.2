# ============================================================
# Build stage
# ============================================================
FROM php:8.2-fpm-alpine AS build

WORKDIR /var/www

# System deps + PHP build deps
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    ffmpeg \
    linux-headers \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    bcmath \
    pcntl \
    sockets \
    gd

# Redis (pin version, deterministic)
RUN pecl install redis-6.3.0 \
 && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files FIRST (cache-friendly)
COPY composer.json composer.lock ./

# Install prod dependencies ONLY
# --no-scripts avoids artisan before files exist
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Copy application source
COPY . .

# REQUIRED directories BEFORE artisan
RUN mkdir -p \
    bootstrap/cache \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
 && chown -R www-data:www-data bootstrap storage \
 && chmod -R 775 bootstrap storage

# Run safe artisan commands (NO key generation)
RUN php artisan package:discover --ansi \
 && php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

# ============================================================
# Runtime stage
# ============================================================
FROM php:8.2-fpm-alpine AS production

WORKDIR /var/www

# Runtime-only libs
RUN apk add --no-cache \
    postgresql-libs \
    libzip \
    curl \
    ffmpeg

# Copy PHP extensions & config
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=build /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copy app
COPY --from=build --chown=www-data:www-data /var/www /var/www

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
