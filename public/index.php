<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 入口文件 ]

// 调试模式开关
define("APP_DEBUG", true);

// 定义应用目录
define('APP_PATH', __DIR__ . '/../app/');

// 定义CMF目录
define('CMF_PATH', __DIR__ . '/../simplewind/cmf/');

// 定义插件目录
define('PLUGINS_PATH', __DIR__ . '/plugins/');

// 定义扩展目录
define('EXTEND_PATH', __DIR__ . '/../simplewind/extend/');
define('VENDOR_PATH', __DIR__ . '/../simplewind/vendor/');

// 定义应用的运行时目录
define('RUNTIME_PATH', __DIR__ . '/../data/runtime/');

// 定义CMF 版本号
define('THINKCMF_VERSION', '5.0.0');

// 加载框架基础文件
require __DIR__ . '/../simplewind/thinkphp/base.php';

// 执行应用
\think\App::run()->send();
