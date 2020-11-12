<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace think\app;

use Closure;
use think\App;
use think\exception\HttpException;
use think\facade\Env;
use think\facade\Lang;
use think\Request;
use think\Response;

/**
 * 多应用模式支持
 */
class MultiApp
{

    /** @var App */
    protected $app;

    /**
     * 应用名称
     * @var string
     */
    protected $name;

    /**
     * 应用名称
     * @var string
     */
    protected $appName;

    /**
     * 应用路径
     * @var string
     */
    protected $path;

    public function __construct(App $app)
    {
        $this->app  = $app;
        $this->name = $this->app->http->getName();
        $this->path = $this->app->http->getPath();
    }

    /**
     * 多应用解析
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        if (!$this->parseMultiApp()) {
            return $next($request);
        }

        return $this->app->middleware->pipeline('app')
            ->send($request)
            ->then(function ($request) use ($next) {
                return $next($request);
            });
    }

    /**
     * 获取路由目录
     * @access protected
     * @return string
     */
    protected function getRoutePath(): string
    {
        return 'route' . DIRECTORY_SEPARATOR;
    }

    /**
     * 解析多应用
     * @return bool
     */
    protected function parseMultiApp(): bool
    {
        $scriptName = $this->getScriptName();
        $defaultApp = $this->app->config->get('app.default_app') ?: 'index';

        if ($this->name || ($scriptName && !in_array($scriptName, ['index', 'router', 'think', 'api']))) {
            $appName = $this->name ?: $scriptName;
            $this->app->http->setBind();
        } else {
            // 自动多应用识别
            $this->app->http->setBind(false);
            $appName       = null;
            $this->appName = '';

            $bind = $this->app->config->get('app.domain_bind', []);

            if (!empty($bind)) {
                // 获取当前子域名
                $subDomain = $this->app->request->subDomain();
                $domain    = $this->app->request->host(true);

                if (isset($bind[$domain])) {
                    $appName = $bind[$domain];
                    $this->app->http->setBind();
                } elseif (isset($bind[$subDomain])) {
                    $appName = $bind[$subDomain];
                    $this->app->http->setBind();
                } elseif (isset($bind['*'])) {
                    $appName = $bind['*'];
                    $this->app->http->setBind();
                }
            }

            if (!$this->app->http->isBind()) {
                $path = $this->app->request->pathinfo();
                $map  = $this->app->config->get('app.app_map', []);
                $deny = $this->app->config->get('app.deny_app_list', []);
                $name = current(explode('/', $path));

                if (strpos($name, '.')) {
                    $name = strstr($name, '.', true);
                }

                if (isset($map[$name])) {
                    if ($map[$name] instanceof Closure) {
                        $result  = call_user_func_array($map[$name], [$this->app]);
                        $appName = $result ?: $name;
                    } else {
                        $appName = $map[$name];
                    }
                } elseif ($name && (false !== array_search($name, $map) || in_array($name, $deny))) {
                    throw new HttpException(404, 'app not exists:' . $name);
                } elseif ($name && isset($map['*'])) {
                    $appName = $map['*'];
                } else {
                    $appName = $name ?: $defaultApp;
                    $appPath = $this->path ?: $this->app->getBasePath() . $appName . DIRECTORY_SEPARATOR;

                    if (!is_dir($appPath)) {
//                        $express = $this->app->config->get('app.app_express', false);
//                        if ($express) {
//                            $this->setApp($defaultApp);
//                            return true;
//                        } else {
//                            return false;
//                        }
                    }
                }

                if ($appName) {
                    $routeFile = $this->app->getBasePath() . $appName . DIRECTORY_SEPARATOR . 'route.php';
                    if (is_file($routeFile)) {
                        include $routeFile;
                    }

//                    $routePath = $this->app->getBasePath() . $appName . DIRECTORY_SEPARATOR . 'route' . DIRECTORY_SEPARATOR;
//                    if (is_dir($routePath)) {
//                        $files = glob($routePath . '*.php');
//                        foreach ($files as $file) {
//                            include_once $file;
//                        }
//                    }
                }

                if (defined('APP_NAMESPACE') && APP_NAMESPACE == 'api') {
                    $coreApps = glob($this->app->getRootPath() . 'vendor/thinkcmf/cmf-api/src/*', GLOB_ONLYDIR);

                    foreach ($coreApps as $coreAppPath) {
                        $routeFile = $coreAppPath . DIRECTORY_SEPARATOR . 'route.php';

                        if (file_exists($routeFile)) {
                            include $routeFile;
                        }
                    }
                }


                if ($name) {
                    $this->app->request->setRoot('/' . $name);
                    $this->app->request->setPathinfo(strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : '');
                }
            }
        }

        $this->setApp($appName ?: $defaultApp);
        return true;
    }

    /**
     * 获取当前运行入口名称
     * @access protected
     * @codeCoverageIgnore
     * @return string
     */
    protected function getScriptName(): string
    {
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $file = $_SERVER['SCRIPT_FILENAME'];
        } elseif (isset($_SERVER['argv'][0])) {
            $file = realpath($_SERVER['argv'][0]);
        }

        return isset($file) ? pathinfo($file, PATHINFO_FILENAME) : '';
    }

    /**
     * 设置应用
     * @param string $appName
     */
    protected function setApp(string $appName): void
    {
        $this->appName = $appName;
        $this->app->http->name($appName);

        $appPath = $this->path ?: $this->app->getBasePath() . $appName . DIRECTORY_SEPARATOR;

        $this->app->setAppPath($appPath);
        // 设置应用命名空间
        $appNamespace = defined('APP_NAMESPACE') ? APP_NAMESPACE : 'app';
        $this->app->setNamespace($this->app->config->get('app.app_namespace') ?: $appNamespace . '\\' . $appName);

        $this->loadVendorApp($appName, $appPath);
        if (is_dir($appPath)) {
            $this->app->setRuntimePath($this->app->getRuntimePath() . $appName . DIRECTORY_SEPARATOR);
            $this->app->http->setRoutePath($this->getRoutePath());

            //加载应用
            $this->loadApp($appName, $appPath);
        }
    }

    protected function loadVendorApp(string $appName, string $appPath)
    {
        $langSet = $this->app->lang->getLangSet();
        $this->app->lang->load([
            root_path() . "vendor/thinkcmf/cmf/src/lang/{$langSet}.php",
        ]);

        // 加载核心应用公共语言包
        $coreApps = ['admin', 'user'];
        foreach ($coreApps as $app) {
            $this->app->lang->load([
                root_path() . "vendor/thinkcmf/cmf-app/src/{$app}/lang/{$langSet}.php",
                root_path() . "vendor/thinkcmf/cmf-app/src/{$app}/lang/{$langSet}/common.php"
            ]);
        }
    }

    /**
     * 加载应用文件
     * @param string $appName 应用名
     * @return void
     */
    protected function loadApp(string $appName, string $appPath): void
    {
        if (is_file($appPath . 'common.php')) {
            include_once $appPath . 'common.php';
        }

        $files = [];

        $files = array_merge($files, glob($appPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));

        foreach ($files as $file) {
            $this->app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        }

        if (is_file($appPath . 'event.php')) {
            $this->app->loadEvent(include $appPath . 'event.php');
        }

        if (is_file($appPath . 'middleware.php')) {
            $this->app->middleware->import(include $appPath . 'middleware.php', 'app');
        }

        if (is_file($appPath . 'provider.php')) {
            $this->app->bind(include $appPath . 'provider.php');
        }

        // 加载应用默认语言包
        $this->app->loadLangPack($this->app->lang->defaultLangSet());
    }

}
