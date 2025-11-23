FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql intl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini