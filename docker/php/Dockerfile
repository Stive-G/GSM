FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensions utiles Symfony
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql zip opcache

# MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
