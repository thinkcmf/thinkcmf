<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\logic\HookLogic;
use cmf\controller\AdminBaseController;
use app\admin\model\HookModel;
use app\admin\model\PluginModel;
use app\admin\model\HookPluginModel;

/**
 * Class HookController 钩子管理控制器
 * @package app\admin\controller
 */
class HookController extends AdminBaseController
{
    /**
     * 钩子管理
     * @adminMenu(
     *     'name'   => '钩子管理',
     *     'parent' => 'admin/Plugin/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '钩子管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $hookModel = new HookModel();
        $hooks     = $hookModel->select();
        $this->assign('hooks', $hooks);
        return $this->fetch();
    }

    /**
     * 钩子插件管理
     * @adminMenu(
     *     'name'   => '钩子插件管理',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '钩子插件管理',
     *     'param'  => ''
     * )
     */
    public function plugins()
    {
        $hook        = $this->request->param('hook');
        $pluginModel = new PluginModel();
        $plugins     = $pluginModel
            ->field('a.*,b.hook,b.plugin,b.list_order,b.status as hook_plugin_status,b.id as hook_plugin_id')
            ->alias('a')
            ->join('hook_plugin b', 'a.name = b.plugin')
            ->where('b.hook', $hook)
            ->order('b.list_order asc')
            ->select();
        $this->assign('plugins', $plugins);
        return $this->fetch();
    }

    /**
     * 钩子插件排序
     * @adminMenu(
     *     'name'   => '钩子插件排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '钩子插件排序',
     *     'param'  => ''
     * )
     */
    public function pluginListOrder()
    {
        $hookPluginModel = new HookPluginModel();
        parent::listOrders($hookPluginModel);

        $this->success("排序更新成功！");
    }

    /**
     * 同步钩子
     * @adminMenu(
     *     'name'   => '同步钩子',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '同步钩子',
     *     'param'  => ''
     * )
     */
    public function sync()
    {

        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

        array_push($apps, 'cmf', 'admin', 'user', 'swoole');

        foreach ($apps as $app) {
            HookLogic::importHooks($app);
        }

        return $this->fetch();
    }


}
