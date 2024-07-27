FROM php:8.0-fpm-alpine

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN set -ex \
    	&& apk --no-cache add postgresql-dev nodejs yarn npm zip unzip php-zip\
    	&& docker-php-ext-install pdo pdo_pgsql

RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
        && pecl install redis \
        && docker-php-ext-enable redis.so

RUN apk add --no-cache libpng libpng-dev && docker-php-ext-install gd && apk del libpng-dev

WORKDIR /var/www/html
