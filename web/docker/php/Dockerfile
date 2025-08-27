FROM php:8.2-fpm-alpine

RUN set -eux; \
    apk add --no-cache $PHPIZE_DEPS icu-dev oniguruma-dev libzip-dev; \
    docker-php-ext-install pdo pdo_mysql; \
    php -v

WORKDIR /var/www/html