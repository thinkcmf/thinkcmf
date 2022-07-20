ThinkCMF 6.0.7 让你更自由地飞
===============
欢迎入坑，有问题请及时提交issue!

### 主要特性
* 框架协议依旧为`MIT`,让你更自由地飞
* 基于`ThinkPHP 6.0`重构，核心代码兼容5.1版本，保证老用户最小升级成本
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
>php8.0  
>mysql 5.7+  
>打开rewrite

### 最低环境要求
>php7.4.0  
>mysql 5.5+ (mysql5.1安装时选择utf8编码，不支持表情符)  
>打开rewrite

### 安装程序
1. public目录做为网站根目录,入口文件在 public/index.php
2. 配置好网站，请访问http://你的域名

enjoy your cmf~!  

### Swagger
#### 开启swagger
调试模式下访问: http://你的域名/swagger

#### 相关文档
**OpenAPI** (https://www.openapis.org)  
**Swagger-PHP** (https://zircote.github.io/swagger-php/)

### Docker
如果需要`docker`下运行`ThinkCMF`,可以使用下面的仓库  
https://gitee.com/thinkcmf/docker

### 升级指导
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

### 废弃功能
* 钩子app_begin（使用module_init）
* 钩子response_send
* 钩子response_end（使用http_end）
* 钩子view_filter

