ThinkCMF 5.0 RC3
===============

### 环境推荐
> php5.5+

> mysql 5.6+

> 打开rewrite


### 最低环境要求
> php5.4+

> mysql 5.5+ (mysql5.1稍后兼容)

> 打开rewrite

### 演示站点
http://demo5.thinkcmf.com/admin/   
用户名/密码:demo/thinkcmf

### 自动安装(测试版)
> 之前安装过 cmf5的同学,请手动创建`data/install.lock`文件

代码已经加入自动安装程序,如果你在安装中有任何问题请提交 issue, 无法安装成功时请尝试下面
的`手动安装步骤`.

enjoy your cmf~!

### 手动安装步骤

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

3.创建`data/install.lock`文件

4.把 public目录做为网站根目录,入口文件在 public/index.php

5.后台
你的域名/admin  
用户名/密码:admin/111111

### 系统更新
如果您是已经安装过 cmf5的用户,请查看 update 目录下的 sql 升级文件,根据自己的下载的程序版本进行更新

### API开发 (支持app,小程序,web)
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

### 更新日志
#### 5.0.170505
[核心]
* 完善用户注册流程
* 完善插件功能
* 增加手机验证码发送钩子
* 增加手机验证码发送演示插件
* 增加用户邮箱绑定
* 增加用户手机绑定
* 增加常用模板钩子
* 增加模板设计图片上传
* 增加用户密码修改
* 增加用户收藏功能
* 增加导航标签,子导航标签增加 `max-level` 设置
* 修复邮箱验证码发送
* 修复windows下获取模板数据时模板文件路径问题
* 修复单文件,多文件上传
* 修复后台首页用户昵称一直显示admin
* 修复 `navigation`,`subNavigation` 标签两个以上不能同时使用问题
* 修复 console 模式报错
* 取消前台有错误时界面刷新

[门户应用]
* 增加文章附件功能
* 优化文章相册

#### 5.0.170422
[核心]
* 完善幻灯片
* 完善后台控制器方法注释
* 增加调试模式下实时更新模板配置
* 增加友情链接图片上传
* 增加应用公共语言包功能
* 增加资源管理
* 增加模板设计数据源层级关系
* 更新jQuery Form版本
* 增加后台菜单类型是否有界面区分
* 增加权限验证时权限规则里没有的规则不用验证
* 增加前台网站信息获取
* 优化后台菜单导入
* 统一排序规则,按从小到大排序
* 修复后台模板管理点更新提示卸载
* 修复标签`NavigationMenu`
* 修复菜单导入时未添加权限规则
* 修复`navigationFolder`设置多个子菜单后会多循环数据
* 修复部分代码php5.4下不兼容
* 修复后台菜单不能添加编辑

[门户应用]
* 完全独立门户应用
* 完善后台页面管理
* 完善面包屑标签`breadcrumb`
* 完善文章分类管理
* 完善文章管理
* 修复文章分类`path`更新
* 优化文章列表标签`articles`
* 优化后台文章分类选择
* 增加前台文章点赞功能
* 增加前台文章搜索功能
* 增加文章列表分页总数获取

#### 5.0.170401
* 完善文件上传
* 增加回收站功能
* 完善友情链接
* 优化网站设置
* 增加后台登陆验证码
* 修复后台用户密码修改
* 修复用户管理-本站用户头像不显示
* 完善前台用户登录注册
* 增加后台菜单导入
* 修复后台菜单列表排序
* 完善导航
* 增加插件钩子管理
* 完善前台模板


