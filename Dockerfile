FROM php:fpm-alpine
ENV TZ "Asia/Shanghai"
RUN sed -i "s/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g" /etc/apk/repositories
RUN apk apk update && apk upgrade \
    && apk add oniguruma oniguruma-dev zlib libstdc++ libzip-dev freetype freetype-dev libpng libpng-dev libjpeg-turbo libjpeg-turbo-dev  $PHPIZE_DEPS\
    && docker-php-ext-install -j$(nproc) pdo_mysql gd bcmath mbstring zip \
    && pecl channel-update pecl.php.net &&pecl install redis && docker-php-ext-enable redis \
COPY . /var/www/html
