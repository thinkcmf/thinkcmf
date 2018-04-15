<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace cmf;


// 加载框架基础文件
require THINKPHP_PATH . 'base.php';


use think\Container;
use think\Facade;
use think\Loader;

// 注册核心类到容器
Container::getInstance()->bind([
    'app'      => think\App::class,
//    'build'                 => Build::class,
//    'cache'                 => Cache::class,
//    'config'                => Config::class,
    'cookie'   => think\Cookie::class,
//    'debug'                 => Debug::class,
//    'env'                   => Env::class,
//    'hook'                  => Hook::class,
    'lang'     => think\Lang::class,
//    'log'                   => Log::class,
//    'middleware'            => Middleware::class,
    'request'  => think\Request::class,
    'response' => think\Response::class,
    'route'    => think\Route::class,
    'session'  => think\Session::class,
//    'url'                   => Url::class,
//    'validate'              => Validate::class,
//    'view'                  => View::class,
//    'rule_name'             => route\RuleName::class,
//    // 接口依赖注入
//    'think\LoggerInterface' => Log::class,
]);

// 注册核心类的静态代理
Facade::bind([
    \think\facade\App::class      => think\App::class,
//    \think\facade\Build::class      => Build::class,
//    \think\facade\Cache::class      => Cache::class,
//    \think\facade\Config::class     => Config::class,
    \think\facade\Cookie::class   => think\Cookie::class,
//    \think\facade\Debug::class      => Debug::class,
//    \think\facade\Env::class        => Env::class,
//    \think\facade\Hook::class       => Hook::class,
    \think\facade\Lang::class     => think\Lang::class,
//    \think\facade\Log::class        => Log::class,
//    \think\facade\Middleware::class => Middleware::class,
    \think\facade\Request::class  => think\Request::class,
    \think\facade\Response::class => think\Response::class,
    \think\facade\Route::class    => think\Route::class,
    \think\facade\Session::class  => think\Session::class,
//    \think\facade\Url::class        => Url::class,
//    \think\facade\Validate::class   => Validate::class,
//    \think\facade\View::class       => View::class,
]);

// 注册类库别名
Loader::addClassAlias([
    'App'      => \think\facade\App::class,
//    'Build'    => \think\facade\Build::class,
//    'Cache'    => \think\facade\Cache::class,
//    'Config'   => \think\facade\Config::class,
    'Cookie'   => \think\facade\Cookie::class,
//    'Db'       => Db::class,
//    'Debug'    => \think\facade\Debug::class,
//    'Env'      => \think\facade\Env::class,
//    'Facade'   => Facade::class,
//    'Hook'     => \think\facade\Hook::class,
    'Lang'     => \think\facade\Lang::class,
//    'Log'      => \think\facade\Log::class,
    'Request'  => \think\facade\Request::class,
    'Response' => \think\facade\Response::class,
    'Route'    => \think\facade\Route::class,
    'Session'  => \think\facade\Session::class,
//    'Url'      => \think\facade\Url::class,
//    'Validate' => \think\facade\Validate::class,
//    'View'     => \think\facade\View::class,
]);


