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
use think\facade\Event;
use think\facade\Db;
use think\facade\Response;
use think\facade\Route;

class InitHookListener
{
    private $app;
    // 行为扩展的执行入口必须是run
    public function handle($param)
    {
        Route::any('plugin/:_plugin/[:_controller]/[:_action]', "\\cmf\\controller\\PluginController@index");
        Route::get('new_captcha', "\\cmf\\controller\\CaptchaController@index");
        if (APP_DEBUG) {
            Route::get('swagger', "\\cmf\\controller\\SwaggerController@index");
        }

        if (!cmf_is_installed()) {
            return;
        }

        $systemHookPlugins = cache('init_hook_plugins_system_hook_plugins');
        if (empty($systemHookPlugins)) {
            try {
                $systemHooks = Db::name('hook')->where(function (Query $query) {
                    $query->where(function (Query $query) {
                        $query->where('app', '=', '')->whereOr('app', '=', 'cmf');
                    })->where('type', 3);
                })->whereOr('type', 1)->column('hook', 'id');

                $systemHookPlugins = Db::name('hook_plugin')->field('hook,plugin')->where('status', 1)
                    ->where('hook', 'in', $systemHooks)
                    ->order('list_order ASC')
                    ->select()->toArray();

                if (!empty($systemHookPlugins)) {
                    cache('init_hook_plugins_system_hook_plugins', $systemHookPlugins, null, 'init_hook_plugins');
                }
            } catch (\Exception $e) {
            }
        }

        if (!empty($systemHookPlugins)) {
            foreach ($systemHookPlugins as $hookPlugin) {
                $hookMethod  = cmf_parse_name($hookPlugin['hook'], 1, false);
                $eventName   = ucfirst($hookMethod);
                $pluginClass = cmf_get_plugin_class($hookPlugin['plugin']);
                Event::listen($eventName, [$pluginClass, $hookMethod]);
            }
        }

        /**--start LangListener--------------------------------------*/
        $this->app = app();
        $langSet   = $this->app->lang->getLangSet();

        $this->app->lang->load([
            root_path() . "vendor/thinkcmf/cmf/src/lang/{$langSet}.php",
        ]);

        // 加载应用公共语言包
        $apps = cmf_scan_dir($this->app->getAppPath() . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            $this->app->lang->load([
                $this->app->getAppPath() . $app . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'common.php',
            ]);
        }
        /**--end LangListener--------------------------------------*/
    }
}
