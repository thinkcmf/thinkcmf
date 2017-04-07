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
        return $this->fetch("profile/favorite");
    }

    /**
     * 用户收藏
     */
    public function do_favorite()
    {
        $key = sp_authcode(I('post.key'));
        if ($key) {
            $authkey  = C("AUTHCODE");
            $key      = explode(" ", $key);
            $authcode = $key[0];
            if ($authcode == C("AUTHCODE")) {
                $table     = $key[1];
                $object_id = $key[2];
                $post      = I("post.");
                unset($post['key']);
                $post['table']     = $table;
                $post['object_id'] = $object_id;

                $uid                  = sp_get_current_userid();
                $post['uid']          = $uid;
                $user_favorites_model = M("UserFavorites");
                $find_favorite        = $user_favorites_model->where(['table' => $table, 'object_id' => $object_id, 'uid' => $uid])->find();
                if ($find_favorite) {
                    $this->error("亲，您已收藏过啦！");
                } else {
                    $post['createtime'] = time();
                    $result             = $user_favorites_model->add($post);
                    if ($result) {
                        $this->success("收藏成功！");
                    } else {
                        $this->error("收藏失败！");
                    }
                }
            } else {
                $this->error("非法操作，无合法密钥！");
            }
        } else {
            $this->error("非法操作，无密钥！");
        }

    }

    /**
     * 用户取消收藏
     */
    public function deleteFavorite()
    {
        $id   = $this->request->param("id", 0, "intval");
        $editData = new UserModel();
        $data = $editData->deleteFavorite($id);
        if ($data) {
            $this->success("取消收藏成功！");
        } else {
            $this->error("取消收藏失败！");
        }
    }
}