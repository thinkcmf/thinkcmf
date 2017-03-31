<?php
/**
 * Created by PhpStorm.
 * User: Powerless
 * Date: 17/03/31
 * Time: 12:46
 */
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
