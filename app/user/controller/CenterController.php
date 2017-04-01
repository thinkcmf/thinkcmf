<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\UserBaseController;

class CenterController extends UserBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    // 会员中心首页
    public function index()
    {
        $this->assign(session('user'));
        return $this->fetch(':center');
    }
}
