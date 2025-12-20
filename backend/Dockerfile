# ---------- Build stage ----------
FROM php:8.2-fpm-alpine AS build

WORKDIR /var/www

# System deps needed to build PHP extensions + composer deps
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
 && docker-php-ext-install pdo pdo_pgsql zip bcmath pcntl sockets gd

# Redis (single, deterministic install)
RUN pecl install redis-5.3.9 \
 && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first
COPY composer.json composer.lock ./

# Install prod deps only
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Copy application
COPY . .

# Laravel optimizations
RUN php artisan key:generate --force \
 && php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

# Permissions
RUN mkdir -p bootstrap/cache storage/logs storage/framework \
 && chown -R www-data:www-data /var/www


# ---------- Runtime stage ----------
FROM php:8.2-fpm-alpine AS production

WORKDIR /var/www

# Runtime-only deps
RUN apk add --no-cache \
    postgresql-libs \
    libzip \
    curl \
    ffmpeg

# PHP extensions already compiled, just copy them
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=build /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copy app
COPY --from=build --chown=www-data:www-data /var/www /var/www

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
