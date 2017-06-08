<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\lib\Storage;
use think\Validate;
use think\Image;
use cmf\controller\UserBaseController;
use app\user\model\UserModel;
use think\Db;

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
                'password'     => 'require|min:6|max:32',
                'repassword'   => 'require|min:6|max:32',
            ]);
            $validate->message([
                'old_password.require' => '旧密码不能为空',
                'old_password.max'     => '旧密码不能超过32个字符',
                'old_password.min'     => '旧密码不能小于6个字符',
                'password.require'     => '新密码不能为空',
                'password.max'         => '新密码不能超过32个字符',
                'password.min'         => '新密码不能小于6个字符',
                'repassword.require'   => '重复密码不能为空',
                'repassword.max'       => '重复密码不能超过32个字符',
                'repassword.min'       => '重复密码不能小于6个字符',
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
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    // 用户头像上传
    public function avatarUpload()
    {
        $file   = $this->request->file('file');
        $result = $file->validate([
            'ext'  => 'jpg,jpeg,png',
            'size' => 1024 * 1024
        ])->move('.' . DS . 'upload' . DS . 'avatar' . DS);

        if ($result) {
            $avatarSaveName = str_replace('//', '/', str_replace('\\', '/', $result->getSaveName()));
            $avatar         = 'avatar/' . $avatarSaveName;
            session('avatar', $avatar);

            return json_encode([
                'code' => 1,
                "msg"  => "上传成功",
                "data" => ['file' => $avatar],
                "url"  => ''
            ]);
        } else {
            return json_encode([
                'code' => 0,
                "msg"  => $file->getError(),
                "data" => "",
                "url"  => ''
            ]);
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

            $avatarPath = "./upload/" . $avatar;

            $avatarImg = Image::open($avatarPath);
            $avatarImg->crop($w, $h, $x, $y)->save($avatarPath);

            $result = true;
            if ($result === true) {
                $storage = new Storage();
                $result  = $storage->upload($avatar, $avatarPath, 'image');

                $userId = cmf_get_current_user_id();
                Db::name("user")->where(["id" => $userId])->update(["avatar" => $avatar]);
                session('user.avatar', $avatar);
                $this->success("头像更新成功！");
            } else {
                $this->error("头像保存失败！");
            }

        }
    }

    /**
     * 绑定手机号或邮箱
     */
    public function binding()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 绑定手机号
     */
    public function bindingMobile()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'username'          => 'require',
                'verification_code' => 'require',
            ]);
            $validate->message([
                'username.require'          => '手机号不能为空',
                'verification_code.require' => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }
            $userModel = new UserModel();
            $log       = $userModel->bindingMobile(['mobile' => $data['username']]);
            switch ($log) {
                case 0:
                    $this->success('手机号绑定成功');
                    break;
                case 2:
                    $this->error('手机号已存在!');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }

    /**
     * 绑定邮箱
     */
    public function bindingEmail()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'username'          => 'require|email',
                'verification_code' => 'require',
            ]);
            $validate->message([
                'username.require'          => '邮箱地址不能为空',
                'username.email'            => '邮箱地址不正确',
                'verification_code.require' => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }
            $userModel = new UserModel();
            $log       = $userModel->bindingEmail(['user_email' => $data['username']]);
            switch ($log) {
                case 0:
                    $this->success('邮箱绑定成功');
                    break;
                case 2:
                    $this->error('邮箱已存在!');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }

}