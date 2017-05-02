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


class FavoriteController extends UserBaseController
{

    /**
     * 个人中心我的收藏列表
     */
    public function index()
    {
        $editData = new UserModel();
        $data = $editData->favorites();
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }

    /**
     * 用户取消收藏
     */
    public function delete()
    {
        $id   = $this->request->param("id", 0, "intval");
        $delete = new UserModel();
        $data = $delete->deleteFavorite($id);
        if ($data) {
            $this->success("取消收藏成功！");
        } else {
            $this->error("取消收藏失败！");
        }
    }

    /**
     * 用户收藏
     */
    public function add()
    {
        $id   = $this->request->param("id", 0, "intval");
        $cid   = $this->request->param("cid", 0, "intval");
        $add = new UserModel();
        $data = $add->addFavorite($id,$cid);
        switch ($data){
            case 0:
                $this->success('收藏成功');
                break;
            case 1:
                $this->error("收藏失败");
                break;
            case 2:
                $this->error("您已收藏过啦");
                break;
            default :
                $this->error('未受理的请求');
        }
    }
}