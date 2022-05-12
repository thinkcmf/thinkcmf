FROM php:8.0-fpm-alpine
LABEL maintainer="ThinkCMF" version="1.0" license="MIT" app.name="ThinkCMF"
ENV TZ "Asia/Shanghai"
RUN sed -i "s/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g" /etc/apk/repositories
RUN apk apk update && apk upgrade \
    && apk add bash git oniguruma oniguruma-dev zlib libstdc++ libzip-dev freetype freetype-dev libpng libpng-dev libjpeg-turbo libjpeg-turbo-dev  $PHPIZE_DEPS\
    && docker-php-ext-configure gd --with-freetype=/usr/include/freetype2/freetype --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql gd bcmath mbstring zip \
    && pecl channel-update pecl.php.net && pecl install redis && docker-php-ext-enable redis
COPY ./docker/shell/get-thinkcmf.sh /var/www/get-thinkcmf.sh
CMD ["/var/www/get-thinkcmf.sh"]