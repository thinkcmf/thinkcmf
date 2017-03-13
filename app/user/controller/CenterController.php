<?php
namespace app\user\controller;

use cmf\controller\UserBaseController;

class CenterController extends UserBaseController
{

    function _initialize()
    {
        parent::_initialize();
    }

    // 会员中心首页
    public function index()
    {
        $this->assign($this->user);
        $this->display(':center');
    }
}
