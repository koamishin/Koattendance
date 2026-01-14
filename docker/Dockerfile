# Production image for coamifee-shop using FrankenPHP (Caddy).
# This intentionally avoids a full Ubuntu base image.

FROM dunglas/frankenphp:1-php8.4 AS base

WORKDIR /var/www/html

# Install required PHP extensions (including Postgres).
# FrankenPHP images include the `install-php-extensions` helper.
RUN install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_pgsql \
    pgsql \
    redis \
    zip

# PHP config
COPY docker/php.ini /usr/local/etc/php/conf.d/99-coamifee.ini

# Install composer dependencies using multi-stage build
FROM composer:2.9.1 AS composer-deps

# Composer resolves platform requirements, so we need required extensions here too.
# Install build deps defensively so intl compiles reliably.
RUN apk add --no-cache icu-dev $PHPIZE_DEPS \
    && docker-php-ext-install intl \
    && apk del --no-network $PHPIZE_DEPS

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-scripts --ignore-platform-reqs

# Build frontend assets using bun
FROM oven/bun:1.3.2-alpine AS assets
WORKDIR /app

# Install PHP 8.4 from Edge for Wayfinder type generation
RUN echo "https://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
    && echo "https://dl-cdn.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories \
    && apk update \
    && apk add --no-cache \
    php84 \
    php84-cli \
    php84-phar \
    php84-mbstring \
    php84-openssl \
    php84-json \
    php84-dom \
    php84-xml \
    php84-tokenizer \
    php84-session \
    php84-ctype \
    php84-simplexml \
    php84-xmlwriter \
    php84-fileinfo \
    php84-pdo \
    php84-pdo_pgsql \
    php84-pgsql \
    php84-bcmath \
    php84-intl \
    php84-gd \
    php84-zip \
    php84-opcache \
    php84-iconv \
    php84-curl \
    && ln -sf /usr/bin/php84 /usr/bin/php

# Set APP_KEY to prevent Laravel from crashing during build
ENV APP_KEY=base64:c4+3/3Xl/3Xl/3Xl/3Xl/3Xl/3Xl/3Xl/3Xl/3Xl/0=

COPY package.json bun.lock ./
RUN bun install --frozen-lockfile
COPY --from=composer-deps /app/vendor ./vendor
COPY . .
RUN bun run build

# Final runtime image
FROM base AS final

COPY . /var/www/html
COPY --from=composer-deps /app/vendor /var/www/html/vendor
COPY --from=assets /app/public/build /var/www/html/public/build

RUN rm -rf /var/www/html/.git /var/www/html/node_modules

# Caddy / FrankenPHP config
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Entrypoint
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["/start.sh"]
