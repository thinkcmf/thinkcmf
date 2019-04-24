<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\behavior;

use think\Container;
use think\exception\HttpResponseException;
use think\facade\Env;
use think\facade\Lang;
use think\facade\Response;

class LangBehavior
{

    protected static $run = false;

    // 行为扩展的执行入口必须是run
    public function run()
    {
        $request = request();

        $app = Container::get('app');

        // 处理API全站跨域
        if ($request->method(true) == 'OPTIONS' && $app->getNamespace() == 'api') {
            $header = [
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => 'GET,POST,PATCH,PUT,DELETE,OPTIONS',
                'Access-Control-Allow-Headers' => 'Authorization,Content-Type,If-Match,If-Modified-Since,If-None-Match,If-Unmodified-Since,X-Requested-With,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId',
            ];

            throw new HttpResponseException(Response::create()->code(204)->header($header));
        }

        if (self::$run) {
            return;
        }
        self::$run = true;

        $langSet = request()->langset();
        Lang::load([
            __DIR__ . '/../lang' . DIRECTORY_SEPARATOR . $langSet . '.php',
        ]);

        // 加载核心应用公共语言包
        $coreApps = ['admin', 'user'];
        foreach ($coreApps as $app) {
            Lang::load([
                Env::get('root_path') . "vendor/thinkcmf/cmf-app/src/{$app}/lang/{$langSet}.php",
                Env::get('root_path') . "vendor/thinkcmf/cmf-app/src/{$app}/lang/{$langSet}/common.php"
            ]);
        }

        // 加载应用公共语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            Lang::load([
                APP_PATH . $app . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'common' . '.php',
            ]);
        }
    }
}