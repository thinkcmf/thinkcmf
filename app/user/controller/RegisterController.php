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

class RegisterController extends HomeBaseController
{

    /**
     * 前台用户注册
     */
    public function index()
    {
        $redirect = $this->request->param("redirect");
        if (empty($redirect)) {
            $redirect = $this->request->server('HTTP_REFERER');
        } else {
            $redirect = base64_decode($redirect);
        }
        session('login_http_referer', $redirect);

        if (cmf_is_user_login()) {
            return redirect($this->request->root().'/');
        } else {
            return $this->fetch(":register");
        }
    }

    /**
     * 前台用户注册提交
     */
    public function doRegister()
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
                $log = $register->registerEmail($user);
            } else if (preg_match('/(^(13\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/', $data['username'])) {
                $user['mobile'] = $data['username'];
                $log = $register->registerMobile($user);
            } else {
                $log = 2;
            }
            $session_login_http_referer = session('login_http_referer');
            $redirect                   = empty($session_login_http_referer) ? $this->request->root() : $session_login_http_referer;
            switch ($log){
                case 0:
                    $this->success('注册成功',$redirect);
                    break;
                case 1:
                    $this->error("您的账户已注册过");
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