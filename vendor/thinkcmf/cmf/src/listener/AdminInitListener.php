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
use think\facade\Event;
use think\facade\Response;
use think\facade\Route;

class AdminInitListener
{
    // 行为扩展的执行入口必须是run
    public function handle($param)
    {
        /**--start AdminMenuLangListener--------------------------------------*/
        $app       = app();
        $langSet   = $app->lang->getLangSet();
        $langFiles = [];

        // 加载核心应用后台菜单语言包
        $coreApps = ['admin', 'user'];
        foreach ($coreApps as $appName) {
            $langFiles[] = root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}/admin_menu.php";
        }

        // 加载应用后台菜单语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $appName) {
            $langFiles[] = APP_PATH . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'admin_menu.php';
        }

        // 加后台菜单动态语言包
        $defaultLangDir = $app->lang->defaultLangSet();
        $langFiles[]    = CMF_DATA . "lang/" . $defaultLangDir . "/admin_menu.php";

        $app->lang->load($langFiles);
        /**--end AdminMenuLangListener--------------------------------------*/

        /**--start AdminLangListener--------------------------------------*/
        $appName = app()->http->getName();
        $app     = app();
        $langSet = $app->lang->getLangSet();

        // 加载核心应用后台语言包
        $coreApps  = ['admin', 'user'];
        $langFiles = [];
        if (in_array($appName, $coreApps)) {
            $langFiles[] = root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}/admin.php";
        }

        // 加载应用后台菜单语言包
        $langFiles[] = $app->getAppPath() . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'admin_menu.php';

        $app->lang->load($langFiles);
        /**--end AdminLangListener--------------------------------------*/
    }
}
