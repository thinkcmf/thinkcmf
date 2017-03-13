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

use think\Hook;
use think\Db;

class InitHookBehavior
{

    // 行为扩展的执行入口必须是run
    public function run(&$param)
    {
        if (isset($_GET['g']) && strtolower($_GET['g']) === 'install') return;

        $data = [];
        if (empty($data)) {
            $plugins = Db::name('plugin')->where('status', 1)->column('hooks', 'name');
            if (!empty($plugins)) {
                foreach ($plugins as $plugin => $hooks) {
                    if (!empty($hooks)) {
                        $hooks = explode(",", $hooks);
                        if (!empty($hooks)) {
                            foreach ($hooks as $hook) {
                                Hook::add($hook, cmf_get_plugin_class($plugin));
                            }
                        }

                    }
                }
            }
        } else {
            //Hook::import($data, false);
        }
    }
}