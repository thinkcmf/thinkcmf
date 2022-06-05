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
use app\admin\model\UserModel;
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

            $this->success(lang('Installed successfully'));
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
            $this->success(lang('Updated successfully'));
        }
    }

    /**
     * 卸载应用
     * @adminMenu(
     *     'name'   => '卸载应用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '卸载应用',
     *     'param'  => ''
     * )
     */
    public function uninstall()
    {
        return $this->fetch();
    }

    /**
     * 卸载应用提交
     * @adminMenu(
     *     'name'   => '卸载应用提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '卸载应用提交',
     *     'param'  => ''
     * )
     */
    public function uninstallPost()
    {
        if ($this->request->isPost()) {
            $appName     = $this->request->param('name', '', 'trim');
            $allowedApps = ['demo', 'portal'];

            if (empty($appName)) {
                $this->error('请输入应用名！');
            }

            if (!in_array($appName, $allowedApps)) {
                $this->error('此应用无法通过网页卸载，请使用命令行程序卸载！');
            }

            $password = $this->request->param('password', '', 'trim');
            if (empty($password)) {
                $this->error('请输入网站创始人后台登录密码！');
            }

            $passwordInDb = UserModel::where('id', 1)->value('user_pass');
            if (!cmf_compare_password($password, $passwordInDb)) {
                $this->error('网站创始人后台登录密码不正确！');
            }

            $result = AppLogic::uninstall($appName);
            if ($result === true) {
                $this->success(lang('Uninstall successful'));
            } else if ($result === false) {
                $this->error(lang('Uninstall failed'));
            } else {
                $this->error($result);
            }
        }
    }


}
