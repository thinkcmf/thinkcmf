ThinkCMF 5.0 开发者预览版
===============
### 环境推荐
> php5.5+

> mysql 5.6+

> 打开rewrite

### 安装步骤


1.创建 thinkcmf5数据库(默认编码utf8mb4),并导入 update/thinkcmf5.sql

2.在 data目录下创建 conf/database.php 文件,内容如下:

```php
<?php

return [
    // 数据库类型
    'type'           => 'mysql',
    // 服务器地址
    'hostname'       => 'localhost',
    // 数据库名
    'database'       => '你的数据库名',
    // 用户名
    'username'       => '你的数据库用户名',
    // 密码
    'password'       => '你的数据库密码',
    // 端口
    'hostport'       => '3306',
    // 数据库编码默认采用utf8
    'charset'        => 'utf8mb4',
    // 数据库表前缀
    'prefix'         => 'cmf_',
    "authcode" => 'CviMdXkZ3vUxyJCwNt',
];
```
更改为你的数据库信息

3.把 public目录做为网站根目录,入口文件在 public/index.php

4.后台
你的域名/admin  
用户名/密码:admin/111111

如果你需要 `api` 开发请下载:  
ThinkCMF5 API :https://github.com/thinkcmf/thinkcmfapi

### 完整版目录结构
```
thinkcmf  根目录
├─api                   api目录(核心版不带)
├─app                   应用目录
│  ├─portal             门户应用目录
│  │  ├─config.php      应用配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  └─ ...            更多类库目录
│  ├─ ...               更多应用
│  ├─command.php        命令行工具配置文件
│  ├─common.php         应用公共(函数)文件
│  ├─config.php         应用(公共)配置文件
│  ├─database.php       数据库配置文件
│  ├─tags.php           应用行为扩展定义文件
│  └─route.php          路由配置文件
├─data                  数据目录
│  ├─conf               动态配置目录
│  ├─runtime            应用的运行时目录（可写）
│  └─ ...               更多
├─public                WEB 部署目录（对外访问目录）
│  ├─api                api入口目录(核心版不带)
│  ├─plugins            插件目录
│  ├─static             静态资源存放目录(css,js,image)
│  ├─themes             前后台主题目录
│  │  ├─admin_simpleboot3  后台默认主题
│  │  └─simpleboot3            前台默认主题
│  ├─upload             文件上传目录
│  ├─index.php          入口文件
│  ├─robots.txt         爬虫协议文件
│  ├─router.php         快速测试文件
│  └─.htaccess          apache重写文件
├─simplewind         
│  ├─cmf                CMF核心库目录
│  ├─extend             扩展类库目录
│  ├─thinkphp           thinkphp目录
│  └─vendor             第三方类库目录（Composer）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
```

### 开发手册
http://www.kancloud.cn/thinkcmf/doc

### QQ群:
ThinkCMF VIP技术群:100828313 (付费)

### 反馈问题
https://github.com/thinkcmf/thinkcmf/issues

