FROM php:8.4-cli-alpine

# intl (exigida pelo Filament) e gd para imagens do painel
RUN apk add --no-cache icu-libs icu-data-full libpng libjpeg-turbo freetype \
    && apk add --no-cache --virtual .build $PHPIZE_DEPS icu-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" intl gd \
    && apk del .build

WORKDIR /var/www
