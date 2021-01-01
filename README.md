ThinkCMF 6.0.0 让你更自由地飞
===============
欢迎入坑，有问题请及时提交issue!

### ThinkCMF6.0主要特性
* 框架协议依旧为`MIT`,让你更自由地飞
* 基于`ThinkPHP 6.0`重构，核心代码兼容5.1版本，保证老用户最小升级成本
* API增加Swagger支持
* 增加`.env`环境配置支持

### 废弃功能
* 钩子app_begin（使用module_init）
* 钩子response_send
* 钩子response_end（使用http_end）
* 钩子view_filter

### 已完成功能
- [x] url美化（这是个大大坑）
- [x] `url()`方法单独维护
- [x] 后台加密码
- [x] 插件功能
- [x] 插件钩子功能
- [x] 补齐相关钩子(action_begin、module_init)
- [x] 迁移behavior到listener
- [x] 应用导航共享
- [x] 应用后台菜单注解
- [x] 应用钩子配置
- [x] 用户操作配置
- [x] URL 规则配置
- [x] 插件和应用的command功能
- [x] 网站安装功能
- [x] `View::share`
- [x] 规范所有`Db::name()`为Model调用
- [x] 单独维护`think-template`,`think-view`
- [x] 单独维护`cmf-route`
- [x] API
- [x] API基顾功能
- [x] API用户基顾功能
- [x] 应用第三方库的支持
- [x] 傻瓜式模板
- [x] 前台模板切换
- [x] 后台多模板机制
- [x] 默认过滤器htmlspecialchars
- [x] 文件上传
- [x] 验证码优化
- [x] Swagger规范
- [x] 强制所有创建，更新，删除操作为POST请求
- [x] 增加基础控制器validateFailError()方法
 
### 开发手册
https://www.thinkcmf.com/docs/cmf6

### Git仓库

1. GitHub:https://github.com/thinkcmf/thinkcmf/tree/6.0 主要仓库
2. 码云:https://gitee.com/thinkcmf/ThinkCMF/tree/6.0 中国镜像


### 环境推荐
> php7.2

> mysql 5.7+

> 打开rewrite


### 最低环境要求
> php7.1+

> mysql 5.5+ (mysql5.1安装时选择utf8编码，不支持表情符)

> 打开rewrite

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


### 待优化功能
- [ ] 总结数据库和模型统一使用规范
- [ ] 应用单独配置目录（待定）
- [ ] 移动Model的逻辑方法到Service里

### 更新日志
#### 6.0.0
* 升级到ThinkPHP6.0
* API增加Swagger支持
* 增加`.env`环境配置支持













