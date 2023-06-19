<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\swagger\controller;

//Demo插件英文名，改成你的插件英文就行了

use cmf\controller\PluginAdminBaseController;

/**
 * Class AdminIndexController.
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
     * Swagger
     * @adminMenu(
     *     'name'   => 'Swagger',
     *     'parent' => 'admin/Plugin/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => 'Swagger',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        return $this->fetch('/swagger');
    }


}
