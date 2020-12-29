<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\listener;

use think\db\Query;
use think\exception\HttpResponseException;
use think\facade\Db;
use think\facade\Response;
use think\facade\Route;

class LangListener
{

    protected static $run = false;

    // 行为扩展的执行入口必须是run
    public function handle($param)
    {
        if (self::$run) {
            return;
        }
        self::$run = true;

        $this->app = app();
        $langSet   = $this->app->lang->getLangSet();
        $this->app->lang->load([
            root_path() . "vendor/thinkcmf/cmf/src/lang/{$langSet}.php",
        ]);

        // 加载核心应用公共语言包
        $coreApps = ['admin', 'user'];
        foreach ($coreApps as $app) {
            $this->app->lang->load([
                root_path() . "vendor/thinkcmf/cmf-app/src/{$app}/lang/{$langSet}/common.php"
            ]);
        }

	    // 加载应用公共语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            $this->app->lang->load([
                APP_PATH . $app . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'common.php',
            ]);
        }
    }
}
