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

use think\db\Query;
use think\exception\HttpResponseException;
use think\facade\Hook;
use think\Db;
use think\Response;
use think\facade\Route;

class InitHookBehavior
{

    // 行为扩展的执行入口必须是run
    public function run($param)
    {
        if (!cmf_is_installed()) {
            return;
        }

        Route::any('plugin/[:_plugin]/[:_controller]/[:_action]', "\\cmf\\controller\\PluginController@index");
        Route::get('new_captcha', "\\cmf\\controller\\CaptchaController@index");

        $request = request();

        // 处理全站跨域
        if ($request->method(true) == 'OPTIONS') {
            $header = [
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => 'GET,POST,PATCH,PUT,DELETE,OPTIONS',
                'Access-Control-Allow-Headers' => 'Authorization,Content-Type,If-Match,If-Modified-Since,If-None-Match,If-Unmodified-Since,X-Requested-With,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId',
            ];
            throw new HttpResponseException(Response::create()->code(204)->header($header));
        }

        $systemHookPlugins = cache('init_hook_plugins_system_hook_plugins');
        if (empty($systemHookPlugins)) {
            $systemHooks = Db::name('hook')->where('type', 1)->whereOr(function (Query $query) {
                $query->where('type', 3)->where('app', ['eq', ''], ['eq', 'cmf'], 'or');
            })->column('hook');

            $systemHookPlugins = Db::name('hook_plugin')->field('hook,plugin')->where('status', 1)
                ->where('hook', 'in', $systemHooks)
                ->order('list_order ASC')
                ->select();
            cache('init_hook_plugins_system_hook_plugins', $systemHookPlugins, null, 'init_hook_plugins');
        }

        if (!empty($systemHookPlugins)) {
            foreach ($systemHookPlugins as $hookPlugin) {
                Hook::add($hookPlugin['hook'], cmf_get_plugin_class($hookPlugin['plugin']));
            }
        }

    }
}