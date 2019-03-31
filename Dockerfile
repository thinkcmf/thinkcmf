FROM php:7.1-apache

MAINTAINER jayknoxqu@gmail.com

#设置容器时区
ENV TZ Asia/Shanghai

#设置程序入口
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    #添加阿里云的镜像源
    && mv /etc/apt/sources.list /etc/apt/sources.list.bak \
    && echo "deb http://mirrors.aliyun.com/debian/ stretch main non-free contrib" >/etc/apt/sources.list \
    && echo "deb http://mirrors.aliyun.com/debian-security stretch/updates main" >>/etc/apt/sources.list \
    && echo "deb http://mirrors.aliyun.com/debian/ stretch-updates main non-free contrib" >>/etc/apt/sources.list \
    && echo "deb http://mirrors.aliyun.com/debian/ stretch-backports main non-free contrib" >>/etc/apt/sources.list \
    #安装程序依赖库
    && apt-get update && apt-get install -y \
              libfreetype6-dev \
              libjpeg62-turbo-dev \
              libpng-dev \
    #安装 PHP 依赖
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    #删除包缓存中的所有包
    && apt-get clean \
    && apt-get autoclean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

#复制代码到PHP容器
COPY . /var/www/html

# 开启URL重写 并且 添加目录权限
RUN a2enmod rewrite \
    && chmod -R 0755 /var/www/html \
    && chown -R www-data:www-data /var/www/html
