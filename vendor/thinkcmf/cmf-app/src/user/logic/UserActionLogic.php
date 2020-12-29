<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\user\logic;

use app\user\model\UserActionModel;

class UserActionLogic
{
    /**
     * 导入应用用户行为
     * @param $app
     * @return array
     * @throws \ReflectionException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function importUserActions($app)
    {
        $userActionConfigFile = cmf_get_app_config_file($app, 'user_action');

        if (file_exists($userActionConfigFile)) {
            $userActionsInFile = include $userActionConfigFile;

            foreach ($userActionsInFile as $userActionKey => $userAction) {

                $userAction['cycle_type'] = empty($userAction['cycle_type']) ? 0 : $userAction['cycle_type'];

                if (!in_array($userAction['cycle_type'], [0, 1, 2, 3])) {
                    $userAction['cycle_type'] = 0;
                }

                if (!empty($userAction['url']) && is_array($userAction['url']) && !empty($userAction['url']['action'])) {
                    $userAction['url'] = json_encode($userAction['url']);
                } else {
                    $userAction['url'] = '';
                }

                $findUserAction = UserActionModel::where('action', $userActionKey)->count();

                $userAction['app'] = $app;

                if ($findUserAction > 0) {
                    UserActionModel::where('action', $userActionKey)
                        ->strict(false)->field(true)
                        ->update([
                            'name' => $userAction['name'],
                            'url'  => $userAction['url']
                        ]);
                } else {
                    $userAction['action'] = $userActionKey;
                    UserActionModel::strict(false)
                        ->field(true)
                        ->insert($userAction);
                }
            }
        }

    }
}
