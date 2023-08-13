ThinkCMF 8.0.0 开发版
===============
欢迎入坑，有问题请及时提交issue!

**`8.0.0`正在紧张开发中，请不要用于正式环境！实际项目请下载最新正式版`6.0.9`**

### 主要特性

* 框架协议依旧为`MIT`,让你更自由地飞
* 基于`ThinkPHP 8.0`
* `API`增加`Swagger`支持
* 增加`.env`环境配置支持
* 增加`Docker`运行环境

### 功能列表

- [x] 基础网站功能（导航、幻灯片、友情链接）
- [x] 后台多角色权限管理
- [x] 云存储
- [x] 微信小程序
- [x] API
- [x] API基础功能
- [x] API用户基础功能
- [x] 傻瓜式模板
- [x] 后台模板设计
- [x] 后台不存在模板文件检测并切换到默认模板
- [x] 后台加密码
- [x] 全站静态文件CDN切换
- [x] 多应用
- [x] 前台多模板
- [x] 后台多模板
- [x] 模板命名空间
- [x] 多语言
- [x] 插件功能
- [x] 插件钩子功能
- [x] 插件在线安装
- [x] 应用在线安装
- [x] 模板在线安装
- [x] 数据库迁移
- [x] 插件和应用命令行工具
- [x] 轻量级命令行工具`php think cli`
- [x] 应用发布打包工具
- [x] 插件发布打包工具
- [x] 模板发布打包工具
- [x] 应用支持`composer`第三方库
- [x] 插件支持`composer`第三方库
- [x] 邮件发送
- [x] Docker容器
- [x] API支持`Swagger`
- [x] `Swagger`规范
- [x] `URL`美化
- [x] 应用导航共享
- [x] 应用后台菜单注解
- [x] 应用钩子配置
- [x] 用户操作配置
- [x] URL规则配置
- [x] 网站安装功能
- [x] 会员管理
- [x] 默认过滤器`htmlspecialchars`
- [x] 文件上传
- [x] 验证码优化
- [x] 强制所有创建，更新，删除操作为POST请求
- [x] 基础控制器`validateFailError()`方法

### 开发手册

https://www.thinkcmf.com/docs/cmf6

### Git仓库

1. 码云:https://gitee.com/thinkcmf/ThinkCMF 主要仓库
2. GitHub:https://github.com/thinkcmf/thinkcmf 国际镜像

### 环境推荐

> php8.1  
> mysql 5.7+  
> 打开rewrite

### 最低环境要求

> php8.0.0  
> mysql 5.5+ (mysql5.1安装时选择utf8编码，不支持表情符)  
> 打开rewrite

### 安装程序

1. public目录做为网站根目录,入口文件在 public/index.php
2. 配置好网站，请访问http://你的域名

enjoy your cmf~!

### Swagger

#### 开启swagger

后台安装 `Swagger`或`Swagger3`插件

#### 相关文档

**OpenAPI** (https://www.openapis.org)  
**Swagger-PHP** (https://zircote.github.io/swagger-php/)

### Docker

如果需要`docker`下运行`ThinkCMF`,可以使用下面的仓库  
https://gitee.com/thinkcmf/docker

### 升级指导

#### 6.0.9升级到8.0.0

1. 更改根目录`composer.json`的`require`下列包版本

```json
"php": ">=8.0.0",
"thinkcmf/cmf-app": "^8.0.0",
"thinkcmf/cmf-api": "^8.0.0",
```
2. `composer update`
3. 后台所有非ajax的`POST`请求，改为`GET`请求
4. 如自定义后台模板未用`admin.js`,请注意所有POST请求时在`header`中增加`XX-Device-Type`和`Authorization`

### 更新日志

#### 8.0.0
* 升级到ThinkPHP8.0
* 增加本地文件上传
* 增加`admin.js`对`RESTful API`支持
* 后台所有POST请求需要传token
* 优化模板标签库加载忽略不存在标签库
* 优化后台模板

