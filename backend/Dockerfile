# ---------- Build stage ----------
FROM php:8.2-fpm-alpine AS build

ARG COMPOSER_HOME=/composer
ENV PATH="${COMPOSER_HOME}/vendor/bin:${PATH}"
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

WORKDIR /var/www

# Build deps
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    build-base \
    bash \
    linux-headers \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo pdo_pgsql zip bcmath pcntl sockets gd

# Redis PECL
RUN pecl install redis-6.3.0 \
 && docker-php-ext-enable redis

# Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy full source FIRST (ensures artisan exists for package discovery)
COPY . .

# Ensure necessary dirs exist and are writable for composer / artisan
RUN mkdir -p bootstrap/cache storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chown -R www-data:www-data bootstrap/cache storage || true

# Install production deps (with scripts so package commands are discovered)
RUN set -eux; \
    composer install \
      --no-dev \
      --no-interaction \
      --prefer-dist \
      --optimize-autoloader \
      --classmap-authoritative

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 bootstrap/cache storage

# ---------- Runtime stage ----------
FROM php:8.2-fpm-alpine AS production

WORKDIR /var/www

# runtime deps only
RUN apk add --no-cache postgresql-libs libzip curl ffmpeg bash

# copy PHP extensions and config
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=build /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# copy app (includes vendor/)
COPY --from=build /var/www /var/www

# ensure permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 bootstrap/cache storage

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
