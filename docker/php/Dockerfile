FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip \
    libzip-dev libicu-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql zip opcache gd \
    && rm -rf /var/lib/apt/lists/*

# MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP config (memory limit, etc.)
COPY docker/php/conf.d/*.ini /usr/local/etc/php/conf.d/
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh \
    && sed -i '1s/^\xEF\xBB\xBF//' /entrypoint.sh \
    && chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

WORKDIR /var/www/html

# Fix permissions Symfony (cache/log) pour éviter monolog "permission denied"
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chown -R www-data:www-data /var/www/html/var \
    && chmod -R 775 /var/www/html/var
