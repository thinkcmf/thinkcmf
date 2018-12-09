ThinkCMF 5.1.0开发版，仅限学习使用
===============

### 系列讲座
https://www.thinkcmf.com/college.html

### ThinkCMF5.1主要特性
* 基于全新 ThinkPHP5.1开发
* 更规范的代码,遵循PSR-2命名规范和PSR-4自动加载规范
* 更规范的数据库设计
* 前后台完全基于bootstrap3
* 增加 api 模块（需单独下载）
* 支持 composer 管理第三方库
* 核心化：独立核心代码包
* 应用化：开发者以应用的形式增加项目模模块
* 插件化：更强的插件机制，开发者以插件形式扩展功能
* 模板化：模板完全傻瓜式，用户无须改动任何代码即可在后台完成模板设计和配置
* 增加 URL美化功能，支持别名设置，更简单
* 独立的回收站功能，可以管理所有应用临时删除的数据
* 统一的资源管理，相同文件只保存一份
* 注解式的后台菜单管理功能，方便开发者代码管理后台菜单
* 文件存储插件化，默认支持七牛文件存储插件
* 模板制作标签化，内置多个cmf标签，方便小白用户
* 更人性化的导航标签，可以随意定制 html 结构
* 后台首页插件化，用户可以定制的网站后台首页

### 环境推荐
> php7.1

> mysql 5.6+

> 打开rewrite


### 最低环境要求
> php5.6+

> mysql 5.5+ (mysql5.1安装时选择utf8编码，不支持表情符)

> 打开rewrite


### 运行环境配置教程
https://www.thinkcmf.com/topic/1502.html


### 自动安装
> 之前安装过 cmf5的同学,请手动创建`data/install.lock`文件

代码已经加入自动安装程序,如果你在安装中有任何问题请提交 issue!

1. public目录做为网站根目录,入口文件在 public/index.php
2. 配置好网站，请访问http://你的域名

enjoy your cmf~!

### 系统更新
如果您是已经安装过 cmf5的用户,请查看 update 目录下的 sql 升级文件,根据自己的下载的程序版本进行更新

### 完整版目录结构
```
thinkcmf  根目录
├─api                     api目录
├─app                     应用目录
│  ├─portal               门户应用目录
│  │  ├─config.php        应用配置文件
│  │  ├─common.php        模块函数文件
│  │  ├─controller        控制器目录
│  │  ├─model             模型目录
│  │  └─ ...              更多类库目录
│  ├─ ...                 更多应用
│  ├─app.php              应用(公共)配置文件
│  ├─command.php          命令行工具配置文件
│  ├─common.php           应用公共(函数)文件
│  ├─database.php         数据库配置文件
│  ├─tags.php             应用行为扩展定义文件
├─data                    数据目录（可写）
│  ├─config               动态配置目录
│  ├─route                动态路由目录
│  ├─runtime              应用的运行时目录（可写）
│  └─ ...                 更多
├─public                  WEB 部署目录（对外访问目录）
│  ├─plugins              插件目录
│  ├─static               静态资源存放目录(css,js,image)
│  ├─themes               前后台主题目录
│  │  ├─admin_simpleboot3 后台默认主题
│  │  └─simpleboot3       前台默认主题
│  ├─upload               文件上传目录
│  ├─api.php              api入口目录
│  ├─index.php            入口文件
│  ├─robots.txt           爬虫协议文件
│  ├─router.php           快速测试文件
│  └─.htaccess            apache重写文件
├─extend                  扩展类库目录
├─vendor                  第三方类库目录（Composer）
│  ├─thinkphp             thinkphp目录
│  └─...             
├─composer.json           composer 定义文件
├─LICENSE.txt             授权说明文件
├─README.md               README 文件
├─think                   命令行入口文件
```

### 开发手册
http://www.kancloud.cn/thinkcmf/doc

### QQ群:
`ThinkCMF 官方交流群`:316669417  
   
`ThinkCMF 高级交流群`:100828313 (付费)  
高级群专属权益:  
第一波:两个后台风格(ThinkCMF官网风格后台主题,蓝色风格后台主题)  
第二波:ThinkCMF5完全开发手册离线版(PDF,EPUB,MOBI格式)  
更多专属权益正在路上...

`ThinkCMF 铲屎官交流群`:415136742 (生活娱乐，为有喵的猿人准备)

### 话题专区
http://www.thinkcmf.com/topic/index/index/cat/11.html

### 反馈问题
https://github.com/thinkcmf/thinkcmf/issues

### 更新日志
#### 5.1.0
[核心]
* 升级`ThinkCMF 5.0`到`ThinkPHP 5.1`





