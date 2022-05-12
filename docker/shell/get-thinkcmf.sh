#!/bin/bash
if [[ -f "/var/www/html/public/api.php" ]]; then
say="服务启动成功"
else
git clone --depth 1 -b 6.0 https://gitee.com/thinkcmf/ThinkCMF thinkcmf && cp -r thinkcmf/* ./ && rm -rf thinkcmf
say="ThinkCMF安装成功"
fi
echo $say
chown -R www-data:www-data /var/www/html
php-fpm