<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\admin\logic;

use think\Db;

class HookLogic
{
    /**
     * 导入应用钩子
     * @param $app
     * @return array
     * @throws \ReflectionException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function importHooks($app)
    {
        $hookConfigFile = cmf_get_app_config_file($app, 'hooks');

        if (file_exists($hookConfigFile)) {
            $hooksInFile = include $hookConfigFile;

            if (empty($hooksInFile) || !is_array($hooksInFile)) {
                return;
            }

            foreach ($hooksInFile as $hookName => $hook) {

                $hook['type'] = empty($hook['type']) ? 2 : $hook['type'];

                if (!in_array($hook['type'], [2, 3, 4]) && !in_array($app, ['cmf', 'swoole'])) {
                    $hook['type'] = 2;
                }

                $findHook = Db::name('hook')->where('hook', $hookName)->count();

                $hook['app'] = $app;

                if ($findHook > 0) {
                    Db::name('hook')->where('hook', $hookName)->strict(false)->field(true)->update($hook);
                } else {
                    $hook['hook'] = $hookName;
                    Db::name('hook')->insert($hook);
                }
            }
        }

    }
}