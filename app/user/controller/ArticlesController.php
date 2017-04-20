<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use think\Db;
use cmf\controller\HomeBaseController;

class ArticlesController extends HomeBaseController
{
    public function index(){
        //获取登录会员信息
        $user= cmf_get_current_user();
//        $result = Db::name('portal_post')->where('user_id',$user['id'])->select();
//        print_r($result);exit;
        $this->assign('user_id',$user['id']);
        return $this->fetch();
    }
}