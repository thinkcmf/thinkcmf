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

use think\exception\ClassNotFoundException;
use think\exception\HttpResponseException;
use think\route\Dispatch;
use think\App as ThinkApp;
use think\Loader;
use think\Db;
/**
 * App 应用管理
 */
class App extends ThinkApp
{
    const VERSION = '5.1.31 LTS';

    /**
     * 当前模块路径
     * @var string
     */
    protected $modulePath;

    /**
     * 应用调试模式
     * @var bool
     */
    protected $appDebug = true;

    /**
     * 应用开始时间
     * @var float
     */
    protected $beginTime;

    /**
     * 应用内存初始占用
     * @var integer
     */
    protected $beginMem;

    /**
     * 应用类库命名空间
     * @var string
     */
    protected $namespace = 'app';

    /**
     * 应用类库后缀
     * @var bool
     */
    protected $suffix = false;

    /**
     * 严格路由检测
     * @var bool
     */
    protected $routeMust;

    /**
     * 应用类库目录
     * @var string
     */
    protected $appPath;

    /**
     * 框架目录
     * @var string
     */
    protected $thinkPath;

    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath;

    /**
     * 运行时目录
     * @var string
     */
    protected $runtimePath;

    /**
     * 配置目录
     * @var string
     */
    protected $configPath;

    /**
     * 路由目录
     * @var string
     */
    protected $routePath;

    /**
     * 配置后缀
     * @var string
     */
    protected $configExt;

    /**
     * 应用调度实例
     * @var Dispatch
     */
    protected $dispatch;

    /**
     * 绑定模块（控制器）
     * @var string
     */
    protected $bindModule;

    /**
     * 初始化
     * @var bool
     */
    protected $initialized = false;



    /**
     * 初始化应用
     * @access public
     * @return void
     */
    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;
        $this->beginTime   = microtime(true);
        $this->beginMem    = memory_get_usage();

        $this->rootPath    = dirname($this->appPath) . DIRECTORY_SEPARATOR;

        //## cmf初始化数据目录配置 ###
        $this->runtimePath = $this->rootPath . 'data/runtime' . DIRECTORY_SEPARATOR;
        $this->routePath   = $this->rootPath . 'data/route' . DIRECTORY_SEPARATOR;
        $this->configPath  = $this->rootPath . 'data/config' . DIRECTORY_SEPARATOR;
        if (defined('RUNTIME_PATH')) {
            $this->runtimePath = RUNTIME_PATH;
        }
        if (defined('ROUTE_PATH')) {
            $this->routePath = ROUTE_PATH;
        }
        if (defined('CONFIG_PATH')) {
            $this->configPath = CONFIG_PATH;
        }
        //## cmf 修改结束 ###

        static::setInstance($this);

        $this->instance('app', $this);

        $this->configExt = $this->env->get('config_ext', '.php');

        // 加载惯例配置文件
        $this->config->set(include $this->thinkPath . 'convention.php');

        // 设置路径环境变量
        $this->env->set([
            'think_path'   => $this->thinkPath,
            'root_path'    => $this->rootPath,
            'app_path'     => $this->appPath,
            'config_path'  => $this->configPath,
            'route_path'   => $this->routePath,
            'runtime_path' => $this->runtimePath,
            'extend_path'  => $this->rootPath . 'extend' . DIRECTORY_SEPARATOR,
            'vendor_path'  => $this->rootPath . 'vendor' . DIRECTORY_SEPARATOR,
        ]);

        // 加载环境变量配置文件
        if (is_file($this->rootPath . '.env')) {
            $this->env->load($this->rootPath . '.env');
        }

        $this->namespace = $this->env->get('app_namespace', $this->namespace);

        //## cmf定义常量 ###
        if (defined('APP_NAMESPACE')) {
            $this->namespace = APP_NAMESPACE;
        }
        //## cmf 修改结束###
        
        $this->env->set('app_namespace', $this->namespace);

        // 注册应用命名空间
        Loader::addNamespace($this->namespace, $this->appPath);

        // 初始化应用
        $this->init();

        // 开启类名后缀
        $this->suffix = $this->config('app.class_suffix');

        // 应用调试模式
        $this->appDebug = $this->env->get('app_debug', $this->config('app.app_debug'));
        $this->env->set('app_debug', $this->appDebug);

        if (!$this->appDebug) {
            ini_set('display_errors', 'Off');
        } elseif (PHP_SAPI != 'cli') {
            //重新申请一块比较大的buffer
            if (ob_get_level() > 0) {
                $output = ob_get_clean();
            }
            ob_start();
            if (!empty($output)) {
                echo $output;
            }
        }

        // 注册异常处理类
        if ($this->config('app.exception_handle')) {
            Error::setExceptionHandler($this->config('app.exception_handle'));
        }

        // 注册根命名空间
        if (!empty($this->config('app.root_namespace'))) {
            Loader::addNamespace($this->config('app.root_namespace'));
        }

        // 加载composer autofile文件
        Loader::loadComposerAutoloadFiles();

        // 注册类库别名
        Loader::addClassAlias($this->config->pull('alias'));

        // 数据库配置初始化
        Db::init($this->config->pull('database'));

        // 设置系统时区
        date_default_timezone_set($this->config('app.default_timezone'));

        // 读取语言包
        $this->loadLangPack();

        // 路由初始化
        $this->routeInit();
    }

    /**
     * 初始化应用或模块
     * @access public
     * @param  string $module 模块名
     * @return void
     */
    public function init($module = '')
    {
        // 定位模块目录
        $module = $module ? $module . DIRECTORY_SEPARATOR : '';
        $path   = $this->appPath . $module;

        // 加载初始化文件
        if (is_file($path . 'init.php')) {
            include $path . 'init.php';
        } elseif (is_file($this->runtimePath . $module . 'init.php')) {
            include $this->runtimePath . $module . 'init.php';
        } else {
            // 加载行为扩展文件
            if (is_file($path . 'tags.php')) {
                $tags = include $path . 'tags.php';
                if (is_array($tags)) {
                    $this->hook->import($tags);
                }
            }

            // 加载公共文件
            if (is_file($path . 'common.php')) {
                include_once $path . 'common.php';
            }

            if ('' == $module) {
                // 加载系统助手函数
                include $this->thinkPath . 'helper.php';

                //## cmf 修改 加载主要配置
                $mainConfigNames = ['app', 'database', 'template', 'paginate'];
                foreach ($mainConfigNames as $configName) {
                    $this->config->load($path . $configName . $this->configExt, $configName);
                }
                //## cmf 修改 结束 ##
            }

            // 加载中间件
            if (is_file($path . 'middleware.php')) {
                $middleware = include $path . 'middleware.php';
                if (is_array($middleware)) {
                    $this->middleware->import($middleware);
                }
            }

            // 注册服务的容器对象实例
            if (is_file($path . 'provider.php')) {
                $provider = include $path . 'provider.php';
                if (is_array($provider)) {
                    $this->bindTo($provider);
                }
            }

            // 自动读取配置文件
            if (is_dir($path . 'config')) {
                $dir = $path . 'config' . DIRECTORY_SEPARATOR;
            } elseif (is_dir($this->configPath . $module)) {
                $dir = $this->configPath . $module;
            }

            $files = isset($dir) ? scandir($dir) : [];

            foreach ($files as $file) {
                if ('.' . pathinfo($file, PATHINFO_EXTENSION) === $this->configExt) {
                    $this->config->load($dir . $file, pathinfo($file, PATHINFO_FILENAME));
                }
            }
        }

        $this->setModulePath($path);

        if ($module) {
            // 对容器中的对象实例进行配置更新
            $this->containerConfigUpdate($module);
        }
    }



    /**
     * 路由初始化 导入路由定义规则
     * @access public
     * @return void
     */
    public function routeInit()
    {
        //## cmf 修改，支持文件 路由配置
        if (is_dir($this->routePath)) {
            $files = scandir($this->routePath);
            foreach ($files as $file) {
                if (strpos($file, '.php')) {
                    $filename = $this->routePath . $file;
                    // 导入路由配置
                    $rules = include $filename;
                    if (is_array($rules)) {
                        $this->route->import($rules);
                    }
                }
            }
        } elseif (is_file($this->routePath)) {
            $rules = include $this->routePath;
            if (is_array($rules)) {
                $this->route->import($rules);
            }
        }
        //## cmf 修改结束 
        if ($this->route->config('route_annotation')) {
            // 自动生成路由定义
            if ($this->appDebug) {
                $suffix = $this->route->config('controller_suffix') || $this->route->config('class_suffix');
                $this->build->buildRoute($suffix);
            }

            $filename = $this->runtimePath . 'build_route.php';

            if (is_file($filename)) {
                include $filename;
            }
        }
    }
}
