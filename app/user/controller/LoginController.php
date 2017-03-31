<?php
/**
 * Created by PhpStorm.
 * User: Powerless
 * Date: 17/03/15
 * Time: 12:46
 */

namespace app\user\controller;

use think\Validate;
use cmf\controller\HomeBaseController;
use app\user\model\UserModel;

class LoginController extends HomeBaseController
{

    /**
     * 登录
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
        if (cmf_is_user_login()) { //已经登录时直接跳到首页
            return redirect($this->request->root().'/');
        } else {
            return $this->fetch(":login");
        }
    }

    /**
     * 登录验证提交
     */
    public function doLogin()
    {
        $validate = new Validate([
            'username' => 'require|min:5|max:32',
            'password' => 'require|min:6|max:32',
            'verify'   => 'require',
        ]);
        $validate->message([
            'username.require' => '用户名不能为空',
            'username.max'     => '用户名不能超过32个字符',
            'username.min'     => '用户名不能小于6个字符',
            'password.require' => '密码不能为空',
            'password.max'     => '密码不能超过32个字符',
            'password.min'     => '密码不能小于6个字符',
            'verify.require'   => '验证码不能为空',
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if (!cmf_captcha_check($data['verify'])) {
            $this->error('验证码错误');
        }
        $login             = new UserModel();
        $user['user_pass'] = $data['password'];
        if ($validate::is($data['username'], 'email')) {
            $user['user_email'] = $data['username'];
            $log                = $login->doEmail($user);
        } else if (preg_match('/(^(13\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/', $data['username'])) {
            $user['mobile'] = $data['username'];
            $log            = $login->doMobile($user);
        } else {
            $user['user_login'] = $data['username'];
            $log                = $login->doName($user);
        }
        $session_login_http_referer = session('login_http_referer');
        $redirect                   = empty($session_login_http_referer) ? $this->request->root() : $session_login_http_referer;
        switch ($log) {
            case 0:
                $this->success('登录成功', $redirect);
                break;
            case 1:
                $this->error('登录密码错误');
                break;
            case 2:
                $this->error('账户不存在');
                break;
            default :
                $this->error('未受理的请求');
        }
    }


}