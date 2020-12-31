ThinkCMF 6.0.0 让你更自由地飞
===============
可以入坑了，欢迎大家测试，有问题请及时提交issue!  

### 更新日志
#### 6.0.0
* 升级到ThinkPHP6.0

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
 
### Swagger
#### 开启swagger
调试模式下访问: http://你的域名/swagger

#### 相关文档
**OpenAPI** (https://www.openapis.org)  
**Swagger-PHP** (https://zircote.github.io/swagger-php/)

### 待测试功能
- [x] 所有基类控制器方法测试
- [x] 所有模板常量测试
- [x] 所有CMF模板标签测试
- [x] 所有系统函数测试

### 待优化功能
- [ ] 总结数据库和模型统一使用规范
- [ ] 应用单独配置目录（待定）
- [ ] 移动Model的逻辑方法到Service里

### 废弃功能
* 钩子app_begin（使用module_init）
* 钩子response_send
* 钩子response_end（使用http_end）
* 钩子view_filter











