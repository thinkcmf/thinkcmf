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
use app\user\model\UserModel;


class BangController extends UserBaseController
{

    /**
     * 绑定账户资料
     */
    public function index()
    {
        $editData = new UserModel();
        $data = $editData->favorites();
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch("profile/bang");
    }

    public function mobile()
    {
        $editData = new UserModel();
        $data = $editData->bangMobile();
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch("profile/bang");
    }

    public function email()
    {
        $editData = new UserModel();
        $data = $editData->favorites();
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch("profile/bang");
    }

    public function oauth()
    {
        $oauth_user_model = M("OauthUser");
        $uid              = sp_get_current_userid();
        $oauths           = $oauth_user_model->where(["uid" => $uid])->select();
        $new_oauths       = [];
        foreach ($oauths as $oa) {
            $new_oauths[strtolower($oa['from'])] = $oa;
        }
        $this->assign("oauths", $new_oauths);
        return $this->fetch();
    }

}