<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\behavior;

use think\Lang;
use think\Request;

class LangBehavior
{

    // 行为扩展的执行入口必须是run
    public function run()
    {
        $request = Request::instance();
        $langSet = $request->langset();
        Lang::load([
            CMF_PATH . 'lang' . DS . $langSet . EXT,
        ]);

        // 加载应用公共语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            Lang::load([
                APP_PATH . $app . DS . 'lang' . DS . $langSet . DS . 'common' . EXT,
            ]);
        }

    }
}