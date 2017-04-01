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
use think\Db;

class IndexController extends UserBaseController
{

    // 前台用户首页 (公开)
    public function index()
    {

        $id = input("get.id", 0, 'intval');

        $users_model = Db::name("User");

        $user = $users_model->where('id',$id)->find();

        if (empty($user)) {
            session('user',null);
            $this->error("查无此人！");
        }

        $this->assign($user);
        $this->display(":index");

    }

    // 前台ajax 判断用户登录状态接口
    function isLogin()
    {
        if (cmf_is_user_login()) {
            $this->success("用户已登录",null,['user'=>cmf_get_current_user()]);
        } else {
            $this->error("此用户未登录!");
        }
    }

    //退出
    public function logout()
    {
        session("user", null);//只有前台用户退出
        return redirect($this->request->root() . "/");
    }

}
