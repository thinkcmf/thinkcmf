FROM php:8.0-fpm-alpine
LABEL maintainer="ThinkCMF" version="1.0" license="MIT" app.name="ThinkCMF"
ENV TZ "Asia/Shanghai"
RUN sed -i "s/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g" /etc/apk/repositories
RUN apk apk update && apk upgrade \
    && apk add  --no-cache --virtual .build-deps oniguruma-dev libpng-dev libzip-dev freetype-dev libjpeg-turbo-dev $PHPIZE_DEPS \
    && apk add bash oniguruma freetype libzip libpng libjpeg-turbo \
    && docker-php-ext-configure gd --with-freetype=/usr/include/freetype2/freetype --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql gd bcmath mbstring zip \
    && pecl channel-update pecl.php.net && pecl install redis && docker-php-ext-enable redis \
    # ---------- clear works ----------
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n" \
    # install composer
   && wget -nv -O /usr/local/bin/composer https://mirrors.aliyun.com/composer/composer.phar \
   && chmod u+x /usr/local/bin/composer \
   && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
COPY ./docker/shell/get-thinkcmf.sh /var/www/get-thinkcmf.sh
CMD ["/var/www/get-thinkcmf.sh"]