#!/bin/bash
if [[ -f "/var/www/html/vendor/thinkcmf/cmf/composer.json" ]]; then
say="ThinkCMF已安装"
else
composer create-project thinkcmf/thinkcmf thinkcmf && cp -r thinkcmf/* ./ && rm -rf thinkcmf
say="ThinkCMF安装成功"
fi
echo $say
chown -R www-data:www-data /var/www/html
php-fpm