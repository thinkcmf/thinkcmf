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

use app\admin\logic\AppLogic;
use cmf\controller\AdminBaseController;
use app\admin\model\AppModel;
use app\admin\model\HookAppModel;
use mindplay\annotations\Annotations;
use think\facade\Cache;
use think\Model;
use think\Validate;

class AppController extends AdminBaseController
{

    /**
     * 应用管理
     * @adminMenu(
     *     'name'   => '应用管理',
     *     'parent' => 'admin/Plugin/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '应用管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $apps = AppLogic::getList();
        $this->assign("apps", $apps);
        return $this->fetch();
    }

    /**
     * 应用安装
     * @adminMenu(
     *     'name'   => '应用安装',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '应用安装',
     *     'param'  => ''
     * )
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $appName = $this->request->param('name', '', 'trim');
            $result  = AppLogic::install($appName);

            if ($result !== true) {
                $this->error($result);
            }

            $this->success('安装成功!');
        }
    }

    /**
     * 应用更新
     * @adminMenu(
     *     'name'   => '应用更新',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '应用更新',
     *     'param'  => ''
     * )
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $appName = $this->request->param('name', '', 'trim');
            $result  = AppLogic::update($appName);

            if ($result !== true) {
                $this->error($result);
            }
            $this->success('更新成功!');
        }
    }

    /**
     * 卸载应用
     * @adminMenu(
     *     'name'   => '卸载应用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '卸载应用',
     *     'param'  => ''
     * )
     */
    public function uninstall()
    {
        if ($this->request->isPost()) {
            $appName = $this->request->param('name', '', 'trim');

            $result = AppLogic::uninstall($id);

            if ($result !== true) {
                $this->error('卸载失败!');
            }

            Cache::clear('init_hook_apps');
            Cache::clear('admin_menus');// 删除后台菜单缓存

            $this->success('卸载成功!');
        }
    }


}
