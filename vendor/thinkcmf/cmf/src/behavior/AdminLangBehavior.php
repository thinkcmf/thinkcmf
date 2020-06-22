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

use think\facade\Env;
use think\facade\Lang;

class AdminLangBehavior
{
    protected static $run = false;

    // 行为扩展的执行入口必须是run
    public function run()
    {
        if (self::$run) {
            return;
        }
        self::$run = true;

        $langSet = request()->langset();

        // 加载核心应用后台菜单语言包
        $coreApps = ['admin', 'user'];
        foreach ($coreApps as $app) {
            Lang::load([
                Env::get('root_path') . "vendor/thinkcmf/cmf-app/src/{$app}/lang/{$langSet}/admin_menu.php",
                Env::get('root_path') . "vendor/thinkcmf/cmf-app/src/{$app}/lang/{$langSet}/admin.php"
            ]);
        }

        // 加载应用后台菜单语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            Lang::load([
                APP_PATH . $app . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'admin_menu.php',
                APP_PATH . $app . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'admin.php',
            ]);
        }

        // 加后台菜单动态语言包
        $defaultLangDir = config('DEFAULT_LANG');
        Lang::load([
            CMF_DATA . "lang/" . $defaultLangDir . "/admin_menu.php"
        ]);
    }
}