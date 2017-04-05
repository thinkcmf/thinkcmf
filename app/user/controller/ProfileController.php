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
use cmf\controller\UserBaseController;
use app\user\model\UserModel;

class ProfileController extends UserBaseController
{

    function _initialize()
    {
        parent::_initialize();
    }

    // 会员中心首页
    public function center()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }
    // 编辑用户资料
    public function editData()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    // 编辑用户资料提交
    public function dataPost()
    {
        $data = $this->request->post();
        $editData = new UserModel();
        if($editData->editData($data)){
            $this->success("保存成功！", url("user/profile/center"));
        }else{
            $this->error("没有新的修改信息！");
        }
    }

    // 个人中心修改密码
    public function editPass()
    {
        return $this->fetch();
    }

    // 个人中心修改密码提交
    public function passPost()
    {
        $validate = new Validate([
            'old_password' => 'require|min:6|max:32',
            'password1' => 'require|min:6|max:32',
            'password2' => 'require|min:6|max:32',
            'verify' =>'require',
        ]);
        $validate->message([
            'old_password.require' => '旧密码不能为空',
            'old_password.max'     => '旧密码不能超过32个字符',
            'old_password.min'     => '旧密码不能小于6个字符',
            'password1.require' => '新密码不能为空',
            'password1.max'     => '新密码不能超过32个字符',
            'password1.min'     => '新密码不能小于6个字符',
            'password2.require' => '重复密码不能为空',
            'password2.max'     => '重复密码不能超过32个字符',
            'password2.min'     => '重复密码不能小于6个字符',
            'verify.require'   => '验证码不能为空',
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if (!cmf_captcha_check($data['verify'])) {
            $this->error('验证码错误');
        }
        $login  = new UserModel();
        $log    = $login->editPass($data);
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

    }

    // 第三方账号绑定
    public function bang()
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

    // 用户头像编辑
    public function avatar()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    // 用户头像上传
    public function avatar_upload()
    {
        $config = [
            'rootPath' => './' . C("UPLOADPATH"),
            'savePath' => './avatar/',
            'maxSize'  => 512000,//500K
            'saveName' => ['uniqid', ''],
            'exts'     => ['jpg', 'png', 'jpeg'],
            'autoSub'  => false,
        ];
        $upload = new \Think\Upload($config, 'Local');//先在本地裁剪
        $info   = $upload->upload();
        //开始上传
        if ($info) {
            //上传成功
            //写入附件数据库信息
            $first = array_shift($info);
            $file  = $first['savename'];
            session('avatar', $file);
            $this->ajaxReturn(sp_ajax_return(["file" => $file], "上传成功！", 1), "AJAX_UPLOAD");
        } else {
            //上传失败，返回错误
            $this->ajaxReturn(sp_ajax_return([], $upload->getError(), 0), "AJAX_UPLOAD");
        }
    }

    // 用户头像裁剪
    public function avatar_update()
    {
        $session_avatar = session('avatar');
        if (!empty($session_avatar)) {
            $targ_w       = I('post.w', 0, 'intval');
            $targ_h       = I('post.h', 0, 'intval');
            $x            = I('post.x', 0, 'intval');
            $y            = I('post.y', 0, 'intval');
            $jpeg_quality = 90;

            $avatar     = $session_avatar;
            $avatar_dir = C("UPLOADPATH") . "avatar/";

            $avatar_path = $avatar_dir . $avatar;

            $image = new \Think\Image();
            $image->open($avatar_path);
            $image->crop($targ_w, $targ_h, $x, $y);
            $image->save($avatar_path);

            $result = true;

            $file_upload_type = C('FILE_UPLOAD_TYPE');
            if ($file_upload_type == 'Qiniu') {
                $upload = new \Think\Upload();
                $file   = ['savepath' => '', 'savename' => 'avatar/' . $avatar, 'tmp_name' => $avatar_path];
                $result = $upload->getUploader()->save($file);
            }
            if ($result === true) {
                $userid = sp_get_current_userid();
                $result = $this->users_model->where(["id" => $userid])->save(["avatar" => 'avatar/' . $avatar]);
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
    public function do_avatar()
    {
        $imgurl = I('post.imgurl');
        //去'/'
        $imgurl               = str_replace('/', '', $imgurl);
        $old_img              = $this->user['avatar'];
        $this->user['avatar'] = $imgurl;
        $res                  = $this->users_model->where(["id" => $this->userid])->save($this->user);
        if ($res) {
            //更新session
            session('user', $this->user);
            //删除旧头像
            sp_delete_avatar($old_img);
        } else {
            $this->user['avatar'] = $old_img;
            //删除新头像
            sp_delete_avatar($imgurl);
        }
        $this->ajaxReturn($res);
    }
}