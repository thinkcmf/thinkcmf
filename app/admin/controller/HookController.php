<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

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
        $plugins     = $pluginModel->field('a.*,b.hook,b.plugin,b.list_order,b.status as hook_plugin_status,b.id as hook_plugin_id')->alias('a')->join('__HOOK_PLUGIN__ b', 'a.name = b.plugin')->where('b.hook', $hook)->select();
        $this->assign('plugins', $plugins);
        return $this->fetch();
    }

    /**
     * 钩子插件启用禁用
     * @adminMenu(
     *     'name'   => '钩子插件启用禁用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '钩子插件启用禁用',
     *     'param'  => ''
     * )
     */
    public function pluginToggle()
    {
        $hookPluginModel = new HookPluginModel();

        $hook   = $this->request->param('hook');
        $plugin = $this->request->param('plugin');

        if ($this->request->param('enable')) {
            $hookPluginModel->save(['status' => 1], ['hook' => $hook, 'plugin' => $plugin]);

            $this->success("启用成功！");
        }

        if ($this->request->param('disable')) {

            $hookPluginModel->save(['status' => 0], ['hook' => $hook, 'plugin' => $plugin]);
            $this->success("禁用成功！");
        }
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


}