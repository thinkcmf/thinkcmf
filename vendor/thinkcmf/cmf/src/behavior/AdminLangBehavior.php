<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\behavior;

use think\Lang;
use think\Request;

class AdminLangBehavior
{

    // 行为扩展的执行入口必须是run
    public function run()
    {
        $request = Request::instance();
        $langSet = $request->langset();

        // 加载应用后台菜单语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            Lang::load([
                APP_PATH . $app . DS . 'lang' . DS . $langSet . DS . 'admin_menu' . EXT,
                APP_PATH . $app . DS . 'lang' . DS . $langSet . DS . 'admin' . EXT,
            ]);
        }

        // 加后台菜单动态语言包
        $defaultLangDir = config('DEFAULT_LANG');
        Lang::load([
            CMF_ROOT . "data/lang/" . $defaultLangDir . "/admin_menu.php"
        ]);
    }
}