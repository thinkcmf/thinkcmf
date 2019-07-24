<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\demo\controller;

//Demo插件英文名，改成你的插件英文就行了

use cmf\controller\PluginAdminBaseController;
use think\Db;

/**
 * Class AdminIndexController.
 *
 * @adminMenuRoot(
 *     'name'   =>'演示插件',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 0,
 *     'icon'   =>'dashboard',
 *     'remark' =>'演示插件入口'
 * )
 */
class AdminIndexController extends PluginAdminBaseController
{
    protected function initialize()
    {
        parent::initialize();
        $adminId = cmf_get_current_admin_id(); //获取后台管理员id，可判断是否登录
        if (!empty($adminId)) {
            $this->assign('admin_id', $adminId);
        }
    }

    /**
     * 演示插件用户列表
     * @adminMenu(
     *     'name'   => '演示插件用户列表',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '演示插件用户列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
//        $result = $this->validate([], 'Demo');
//        if ($result !== true) {
//            $this->error($result);
//        }
        $users = Db::name('user')->limit(0, 5)->select();
        //$demos = PluginDemoModel::all();

        // print_r($demos);

        $this->assign('users', $users);

        return $this->fetch('/admin_index');
    }

    /**
     * 演示插件设置
     * @adminMenu(
     *     'name'   => '演示插件设置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '演示插件设置',
     *     'param'  => ''
     * )
     */
    public function setting()
    {
        $users = Db::name('user')->limit(0, 5)->select();
        //$demos = PluginDemoModel::all();

        // print_r($demos);

        $this->assign('users', $users);

        $this->assign('users', $users);

        return $this->fetch('/admin_index');
    }
}
