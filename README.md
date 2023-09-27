ThinkCMF 8.0.0 让你更自由地飞
===============
欢迎入坑，有问题请及时提交issue!

### 主要特性

* `MIT`开源协议,让你飞得更高,行得更远
* 基于`ThinkPHP 8.0`
* 多应用架构
* 应用中心
* 支持插件机制
* 支持多模板
* 支持模板可视化设计
* 支持`RESTful API`
* 支持`Swagger API`文档
* 支持数据库迁移
* 支持`Docker`运行

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
- [x] 强制所有创建，更新，删除操作携带Token请求
- [x] 基础控制器`validateFailError()`方法
- [x] 支持`.env`环境配置

### 即将废弃
* `app`模式下后台所有`非GET`请求提交接口,请后台模板开发者尽快升级到`API`接口

### 开发手册

https://www.thinkcmf.com/docs/cmf8

### Git仓库

1. 码云:https://gitee.com/thinkcmf/ThinkCMF 主要仓库
2. GitHub:https://github.com/thinkcmf/thinkcmf 国际镜像

### 环境推荐

> PHP 8.1     
> MySQL 5.7+   
> 打开rewrite

### 最低环境要求

> PHP 8.0 (swagger插件要求PHP8.1)   
> MySQL 5.5   
> 打开rewrite

### 安装程序

1. public目录做为网站根目录,入口文件在 public/index.php
2. 配置好网站，请访问http://你的域名

enjoy your cmf~!

### Swagger

#### 开启Swagger

后台应用中心->插件管理安装 `Swagger`插件(要求PHP8.1及以上)

#### 相关文档

**OpenAPI** (https://www.openapis.org)  
**Swagger-PHP** (https://zircote.github.io/swagger-php/)

### Docker

如果需要`Docker`下运行`ThinkCMF`,可以使用下面的仓库  
https://gitee.com/thinkcmf/docker

### 升级指导

#### 6.0.9升级到8.0.0

1. 更改根目录`composer.json`的`require`下列包版本

```json
"php": ">=8.0.0",
"thinkcmf/cmf-app": "^8.0.0",
"thinkcmf/cmf-install": "^8.0.0",
"thinkcmf/cmf-api": "^8.0.0",
"thinkcmf/cmf-appstore": "^2.0",
"thinkcmf/cmf-root": "^2.0"
```
2. `composer update`
3. 后台所有非ajax的`POST`请求，改为`GET`请求
4. 如自定义后台模板未用`admin.js`,请注意所有POST请求时在`header`中增加`XX-Device-Type`和`Authorization`
5. 把`data/config/template.php`中`cmf_admin_default_theme`后台模板改为`admin_default`

#### 6.0.8升级到6.0.9
1. `composer update`

#### 6.0.7升级到6.0.8
1. `composer update`

#### 6.0.6升级到6.0.7
1. `composer update`

#### 6.0.5升级到6.0.6
1. `composer update`

#### 6.0.4升级到6.0.5
1. 根目录`composer.json`的`require-dev`属性值请更新
2. `composer update`

#### 6.0.3升级到6.0.4
1. 安装静态资源包`composer require thinkcmf/cmf-root`
2. 根目录`composer.json`的`minimum-stability`,`require`,`config`属性值请更新
3. `composer update`

#### 6.0.2升级到6.0.3
1. `composer update`

#### 6.0.1升级到6.0.2
1. composer.json文件里的`autoload.psr-4.themes\\`改为`public/themes`
2. 安装应用市场包`composer require thinkcmf/cmf-appstore`
3. `public/themes`,`public/static`静态文件也有更新
4. 删除`public/themes/admin_simpleboot3/admin`目录下的`app_store`目录
5. `composer update`

### 更新日志

#### 8.0.0
* 升级到`ThinkPHP8.0`
* 增加后台管理本地文件上传
* 增加`admin.js`对`RESTful API`支持
* 增加插件`PluginRestAdminBaseController`基类
* 后台所有POST请求需要传token
* 后台使用`RESTful API`
* 优化模板标签库加载忽略不存在标签库
* 优化后台模板
* 更改后台默认模板为`admin_default`
* 增加后台风格`arcoadmin`
* 增加后台模板支持`bootstrap5`
* 完善前台默认模板`default`


#### 6.0.9
* 增强前台模板自由控件功能支持拖拽
* 增加后台API权限管理
* 增加后台API导入
* 增加`Swagger`插件
* 增加模板块控件css样式功能
* 增加模板富文本`rich_text`变量类型
* 增加应用支持系统钩子
* 增加安装程序数据库迁移功能
* 增加插件执行顺序设置
* 增加`cmf_get_file_url`,`cmf_utf8_bom`函数
* 优化后台开发者面板支持插件扩展
* 优化后台插件管理
* 优化后台首页
* 优化插件设置
* 优化权限认证

#### 6.0.8
* 更新TP到`6.0.14`
* 增加应用轻量级命令行第三方库支持
* 增加注册登录和验证码界面第三方验证码支持
* 增加后台管理员个人邮件功能
* 增加应用支持导入系统钩子功能
* 优化上传对话框逻辑
* 优化钩子管理界面
* 优化后台管理员添加编辑逻辑增加安全性
* 优化缓存清理
* 优化路由识别
* 优化系统文件加载
* 修复后台部分URL刷新不加载
* 修复幻灯片页面管理报错
* 补全缺失语言包


#### 6.0.7
* 升级到`tp6.0.13`
* 增加安装时检查API配置
* 增加前台模板自由控件功能
* 增加`widgetsHead`,`widgetsBlock`,`widgetsScript`标签
* 增加`css`标签相同文件不重复引入功能
* 增加`js`标签相同文件不重复引入功能
* 修复后台菜单添加和编辑子菜单父级不选中
* 修复后台菜单编辑删除报错
* 修复应用市场模板升级报错
* 增加`php think cli`支持`/`分隔符
* 修复`tree类`相关bug

#### 6.0.6
* 后台模板设计增强，支持多终端
* 增加命令行卸载应用
* 增加网页卸载应用
* 增加命令行卸载插件
* 增加`tree`标签
* 后台菜单和导航菜单管理使用`tree`标签
* 优化应用插件模板升级安装逻辑
* 优化应用打包格式
* 去除`eval`的使用
* 修复API跨域问题
* 修复邮件验证码获取用户信息错误

#### 6.0.5
* 增加数据库迁移
* 增加模板在线安装
* 增加轻量级命令行工具`php think cli`
* 增加应用发布打包工具
* 增加插件发布打包工具
* 增加模板发布打包工具
* 优化命令行程序
* 修复命令行下常量`APP_PATH`缺失
* 独立`Docker`容器为单独仓库

#### 6.0.4
* 调整PHP版本最低限制为`7.4.0`
* 增加应用在线安装
* 增加应用管理
* 增加安装时更多目录可写检查
* 增加`cmf_get_app_class`函数
* 增加`cmf_is_cli`函数
* 增加`cmf_test_write`函数
* 优化用户资料编辑
* 优化函数`cmf_scan_dir`
* 优化安装时数据库连接处理
* 修复`cmf_curl_get`不支持https
* 修复`/home/slides/{id}`API注解错误
* 修复验证码登录API报错
* 修复回收站删除、还原报错
* 修复API路由加载
* 修复插件更新无法删除旧钩子

#### 6.0.3
* 自定义分页类
* 优化后台模板设计
* 优化后台菜单导入
* 修复验证器使用错误
* 修复路由禁用报错
* 修复插件模板异常类引入错误

#### 6.0.2
* 增加插件市场支持插件在线安装
* 增加后台不存在模板文件检测并切换到默认模板
* 移动swagger功能到插件
* 优化插件后台权限检查
* 修复url美化报错
* 规范env命名，方便编辑器跳转
* 修正themes命名空间
* 修复角色删除问题
* 修复管理员删除问题
* 修复幻灯片删除问题
* 优化用户注册
* 优化后台菜单导入

#### 6.0.1
* 兼容php8.0
* 升级到`tp6.0.7`
* 增加插件后台基类`admin_init`钩子
* 优化cmf版本获取
* 优化`cmf_clear_cache()`函数
* 修复插件URL美化报错
* 修复上传报错
* 修复`demo应用 page/nav`数据源演示报错
* 修复导入后台菜单报错
* 修复url美化问题
* 修复头像上传报错

#### 6.0.0
* 升级到ThinkPHP6.0
* API增加Swagger支持
* 增加`.env`环境配置支持

