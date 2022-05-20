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

class HomeLangListener
{
    // 行为扩展的执行入口必须是run
    public function handle()
    {
        $app       = app();
        $langSet   = $app->lang->getLangSet();
        $langFiles = [];

        // 加载应用前台语言包
        $apps = cmf_scan_dir($app->getAppPath() . '*', GLOB_ONLYDIR);
        foreach ($apps as $appName) {
            $langFiles[] = $app->getAppPath() . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'home.php';
        }

        $app->lang->load($langFiles);
    }
}
