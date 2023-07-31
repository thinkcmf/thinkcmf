<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\system_info\controller;

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
     * PHPINFO
     * @adminMenu(
     *     'name'   => 'PHPINFO',
     *     'parent' => 'admin/Dev/index',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => 'PHPINFO',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        phpinfo();
        return '';
    }


}
