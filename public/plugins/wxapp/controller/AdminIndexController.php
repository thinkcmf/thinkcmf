<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\wxapp\controller; //Demo插件英文名，改成你的插件英文就行了

use cmf\controller\PluginAdminBaseController;

class AdminIndexController extends PluginAdminBaseController
{

    public function _initialize()
    {
        $adminId = cmf_get_current_admin_id();//获取后台管理员id，可判断是否登录
        if (!empty($adminId)) {
            $this->assign("admin_id", $adminId);
        } else {
            $this->error('未登录');
        }
    }

    /**
     * 小程序管理
     * @adminMenu(
     *     'name'   => '小程序管理',
     *     'parent' => 'admin/Plugin/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '小程序管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        $wxappSettings = cmf_get_option('wxapp_settings');

        $wxapps = empty($wxappSettings['wxapps']) ? [] : $wxappSettings['wxapps'];

        $defaultWxapp = empty($wxappSettings['default']) ? [] : $wxappSettings['default'];

        $this->assign('wxapps', $wxapps);
        $this->assign('default_wxapp', $defaultWxapp);

        return $this->fetch('/admin_index');
    }

}
