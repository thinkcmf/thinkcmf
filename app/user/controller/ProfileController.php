<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use think\Validate;
use think\Request;
use think\Image;
use cmf\controller\UserBaseController;
use app\user\model\UserModel;

class ProfileController extends UserBaseController
{

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 会员中心首页
     */
    public function center()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 编辑用户资料
     */
    public function edit()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch('edit');
    }

    /**
     * 编辑用户资料提交
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $data     = $this->request->post();
            $editData = new UserModel();
            if ($editData->editData($data)) {
                $this->success("保存成功！", url("user/profile/center"));
            } else {
                $this->error("没有新的修改信息！");
            }
        } else {
            $this->error("请求错误");
        }
    }

    /**
     * 个人中心修改密码
     */
    public function password()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 个人中心修改密码提交
     */
    public function passwordPost()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'old_password' => 'require|min:6|max:32',
                'password'    => 'require|min:6|max:32',
                'repassword'    => 'require|min:6|max:32',
            ]);
            $validate->message([
                'old_password.require' => '旧密码不能为空',
                'old_password.max'     => '旧密码不能超过32个字符',
                'old_password.min'     => '旧密码不能小于6个字符',
                'password.require'    => '新密码不能为空',
                'password.max'        => '新密码不能超过32个字符',
                'password.min'        => '新密码不能小于6个字符',
                'repassword.require'    => '重复密码不能为空',
                'repassword.max'        => '重复密码不能超过32个字符',
                'repassword.min'        => '重复密码不能小于6个字符',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $login = new UserModel();
            $log   = $login->editPassword($data);
            switch ($log) {
                case 0:
                    $this->success('修改成功');
                    break;
                case 1:
                    $this->error('密码输入不一致');
                    break;
                case 2:
                    $this->error('原始密码不正确');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }

    }

    // 用户头像编辑
    public function avatar()
    {
        dump(session('avatar'));
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    // 用户头像上传
    public function avatarUpload()
    {
        $file = request()->file('file');
        $info = $file->move(ROOT_PATH . 'public/upload/avatar/');
        if ($info) {
            session('avatar', $info->getSaveName());

            $this->success('上传成功', url('Profile/avatarUpload'), ['file' => $info->getSaveName()]);
        } else {
            $this->error($file->getError());
        }
    }

    // 用户头像裁剪
    public function avatarUpdate()
    {
        $avatar = session('avatar');
        if (!empty($avatar)) {
            $w = $this->request->param('w', 0, 'intval');
            $h = $this->request->param('h', 0, 'intval');
            $x = $this->request->param('x', 0, 'intval');
            $y = $this->request->param('y', 0, 'intval');

            $avatar_dir = "/public/upload/avatar/";

            $avatar_path = $avatar_dir . $avatar;

            $avatarImg = Image::open($avatar_path);
            $avatarImg->crop($w, $h, $x, $y)->save($avatar_path);

            $result = true;

            if ($result === true) {
                $uid       = cmf_get_current_user_id();
                $userQuery = Db::name("user");
                $result    = $userQuery->where(["id" => $uid])->save(["avatar" => 'avatar/' . $avatar]);
                session('user.avatar', 'avatar/' . $avatar);
                if ($result) {
                    $this->success("头像更新成功！");
                } else {
                    $this->error("头像更新失败！");
                }
            } else {
                $this->error("头像保存失败！");
            }

        }
    }

    // 保存用户头像
    public function doAvatar()
    {
        $imgurl               = $this->request->param('imgurl');
        $imgurl               = str_replace('/', '', $imgurl);
        $old_img              = $this->user['avatar'];
        $this->user['avatar'] = $imgurl;
        $res                  = $this->users_model->where(["id" => $this->userid])->save($this->user);
        if ($res) {
            //更新session
            session('user', $this->user);
            //删除旧头像
            cmf_delete_avatar($old_img);
        } else {
            $this->user['avatar'] = $old_img;
            //删除新头像
            sp_delete_avatar($imgurl);
        }
        $this->ajaxReturn($res);
    }

}