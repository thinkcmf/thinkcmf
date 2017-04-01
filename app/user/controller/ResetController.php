<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\HomeBaseController;
use think\Validate;
use app\user\model\UserModel;

class ResetController extends HomeBaseController
{

    /**
     * 前台用户忘记密码
     */
    public function index()
    {
        return $this->fetch(":reset");
    }

    /**
     * 前台用户忘记密码提交
     */
    public function doReset()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'code' => 'require',
                'password' => 'require|min:6|max:32',
                'verify' => 'require',
            ]);
            $validate->message([
                'code.require' => '验证码不能为空',
                'password.require' => '密码不能为空',
                'password.max' => '密码不能超过32个字符',
                'password.min' => '密码不能小于6个字符',
                'verify.require' => '验证码不能为空',
            ]);

            $data = $this->request->param();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if(!cmf_captcha_check($data['verify'])){
                $this->error('验证码错误');
            }
            $errMsg = cmf_check_verification_code($data['mobile'],$data['code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }

            $register = new UserModel();
            $user['user_pass']   = $data['password'];
            if ($validate::is($data['username'], 'email')) {
                $user['user_email'] = $data['username'];
                $log = $register->resetEmail($user);
            } else if (preg_match('/(^(13\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/', $data['username'])) {
                $user['mobile'] = $data['username'];
                $log = $register->resetMobile($user);
            } else {
                $log = 2;
            }
            switch ($log){
                case 0:
                    $this->success('密码重置成功',$this->request->root());
                    break;
                case 1:
                    $this->error("您的账户尚未注册");
                    break;
                case 2:
                    $this->error("您输入的账号格式错误");
                    break;
                default :
                    $this->error('未受理的请求');
            }

        } else {
            $this->error("请求错误");
        }

    }
}