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
namespace cmf\listener;

use think\facade\Env;
use think\facade\Lang;

class HomeLangListener
{
    protected static $run = false;

    // 行为扩展的执行入口必须是run
    public function handle()
    {
        if (self::$run) {
            return;
        }
        self::$run = true;

        $app     = app();
        $langSet = $app->lang->getLangSet();

        // 加载核心应用前台通用语言包
        $coreApps = ['admin', 'user'];
        foreach ($coreApps as $appName) {
            $app->lang->load([
                root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}/home.php"
            ]);
        }

        // 加载应用前台通用语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $appName) {
            $app->lang->load([
                APP_PATH . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'home.php',
            ]);
        }

    }
}
