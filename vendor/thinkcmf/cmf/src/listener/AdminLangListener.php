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

use think\facade\Env;
use think\facade\Lang;

class AdminLangListener
{
    public static $appLoaded = [];

    // 行为扩展的执行入口必须是run
    public function handle()
    {
        $appName = app()->http->getName();
        if (!empty(self::$appLoaded[$appName])) {
            return;
        }
        self::$appLoaded[$appName] = true;

        $app     = app();
        $langSet = $app->lang->getLangSet();

        // 加载核心应用后台语言包
        $coreApps = ['admin', 'user'];
        $app->lang->load([
            root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}/admin.php",
        ]);

        // 加载应用后台菜单语言包
        $app->lang->load([
            APP_PATH . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'admin_menu.php',
        ]);

    }
}
