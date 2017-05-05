<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

class UserBaseController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
        $userId = cmf_get_current_user_id();
        if (empty($userId)) {
            $this->error("用户尚未登录", url("user/login/index"));
        }
    }


}