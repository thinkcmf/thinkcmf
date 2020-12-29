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

use think\facade\Db;
use think\facade\Event;

class InitAppHookListener
{

    public static $appLoaded = [];

    // 行为扩展的执行入口必须是run
    public function handle($param)
    {
        $appName = app()->http->getName();
        if (!empty(self::$appLoaded[$appName])) {
            return;
        }
        self::$appLoaded[$appName] = true;

        $this->app = app();
        $langSet   = $this->app->lang->getLangSet();

        // 加载核心应用公共语言包
        $this->app->lang->load([
            root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}.php",
        ]);

        // 加载应用语言包
        $this->app->lang->load([
            APP_PATH . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . '.php',
        ]);

        // 加载应用第三方库
        $appAutoLoadFile = APP_PATH . $appName . '/vendor/autoload.php';
        if (file_exists($appAutoLoadFile)) {
            require_once $appAutoLoadFile;
        }

        if (!cmf_is_installed()) {
            return;
        }

        // 加载应用钩子
        $appHookPluginsCacheKey = "init_hook_plugins_app_{$appName}_hook_plugins";
        $appHookPlugins         = cache($appHookPluginsCacheKey);

        if (empty($appHookPlugins)) {
            $appHooks = Db::name('hook')->where('app', $appName)->column('hook');

            $appHookPlugins = Db::name('hook_plugin')->field('hook,plugin')->where('status', 1)
                ->where('hook', 'in', $appHooks)
                ->order('list_order ASC')
                ->select();
            cache($appHookPluginsCacheKey, $appHookPlugins, null, 'init_hook_plugins');
        }

        if (!empty($appHookPlugins)) {
            foreach ($appHookPlugins as $hookPlugin) {
                $hookMethod  = cmf_parse_name($hookPlugin['hook'], 1, false);
                $eventName   = ucfirst($hookMethod);
                $pluginClass = cmf_get_plugin_class($hookPlugin['plugin']);
                Event::listen($eventName, [$pluginClass, $hookMethod]);
            }
        }
    }
}
