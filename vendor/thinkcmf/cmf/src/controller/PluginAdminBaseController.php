<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

class PluginAdminBaseController extends PluginBaseController
{

    // 初始化
    protected function initialize()
    {
        // 监听admin_init
        $param = ['is_plugin' => true];
        hook('admin_init', $param);
        $adminId = cmf_get_current_admin_id();
        if (!empty($adminId)) {
            if (!$this->checkAccess($adminId)) {
                $this->error("您没有访问权限！");
            }
        } else {
            if ($this->request->isAjax()) {
                $this->error("您还没有登录！", url("admin/Public/login"));
            } else {
                header("Location:" . url("admin/Public/login"));
                exit();
            }
        }
    }

    /**
     *  检查后台用户访问权限
     * @param int $userId 后台用户id
     * @return boolean 检查通过返回true
     */
    private function checkAccess($userId)
    {
        // 如果用户id是1，则无需判断
        if ($userId == 1) {
            return true;
        }

        $pluginName = $this->request->param('_plugin');
        $pluginName = cmf_parse_name($pluginName, 1);
        $controller = $this->request->param('_controller');
        $controller = cmf_parse_name($controller, 1);
        $action     = $this->request->param('_action');

        return cmf_auth_check($userId, "plugin/{$pluginName}/$controller/$action");
    }


}
