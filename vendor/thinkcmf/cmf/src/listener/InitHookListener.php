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

use think\db\Query;
use think\exception\HttpResponseException;
use think\facade\Hook;
use think\Db;
use think\facade\Response;
use think\facade\Route;

class InitHookListener
{

    // 行为扩展的执行入口必须是run
    public function handle($param)
    {
        Route::any('plugin/[:_plugin]/[:_controller]/[:_action]', "\\cmf\\controller\\PluginController@index");
        Route::get('new_captcha', "\\cmf\\controller\\CaptchaController@index");

        if (!cmf_is_installed()) {
            return;
        }


    }
}
