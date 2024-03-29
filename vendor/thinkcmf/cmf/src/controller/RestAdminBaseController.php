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

class RestAdminBaseController extends RestBaseController
{
    public function initialize()
    {
        hook('admin_init');
        if (empty($this->user)) {
            $this->error(['code' => 10001, 'msg' => '登录已失效!']);
        } elseif ($this->userType != 1) {
            $this->error(['code' => 10001, 'msg' => '登录已失效!']);
        }

        $this->checkAccess();
    }

    public function checkAccess()
    {
        $requestMethod = $this->request->method();
        $ruleName      = "admin_api:$requestMethod|{$this->getRoutePath()}";
        if (!cmf_auth_check($this->getUserId(), $ruleName)) {
            $this->error(['code' => 0, 'msg' => '无权限！']);
        }
    }

}
