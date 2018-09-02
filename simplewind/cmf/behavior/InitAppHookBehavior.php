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

use think\db\Query;
use think\Hook;
use think\Db;

class InitAppHookBehavior
{

    // 行为扩展的执行入口必须是run
    public function run(&$param)
    {
        if (!cmf_is_installed()) {
            return;
        }

        $app        = request()->module();

        $appHookPluginsCacheKey = "init_hook_plugins_app_{$app}_hook_plugins";
        $appHookPlugins         = cache($appHookPluginsCacheKey);

        if (empty($appHookPlugins)) {
            $appHooks = Db::name('hook')->where('app', $app)->column('hook');

            $appHookPlugins = Db::name('hook_plugin')->field('hook,plugin')->where('status', 1)
                ->where('hook', 'in', $appHooks)
                ->order('list_order ASC')
                ->select();
            cache($appHookPluginsCacheKey, $appHookPlugins, null, 'init_hook_plugins');
        }

        if (!empty($appHookPlugins)) {
            foreach ($appHookPlugins as $hookPlugin) {
                Hook::add($hookPlugin['hook'], cmf_get_plugin_class($hookPlugin['plugin']));
            }
        }
    }
}