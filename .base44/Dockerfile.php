# Base44 sandbox runtime for the Laravel app in website/.
#
# Only tooling lives in this image (PHP 8.3 + the extensions the app's web
# installer checks for, plus Composer). The application source is bind-mounted
# at /app by docker-compose.base44.yml, so code edits are picked up immediately
# and no application code is ever baked into an image layer.
FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip \
        libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
        libzip-dev libicu-dev libc-client2007e-dev libkrb5-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl imap pcntl pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    HOME=/root
