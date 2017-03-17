<?php
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
            return redirect($this->request->root());
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
            'verify' => 'require',
        ]);
        $validate->message([
            'username.require' => '用户名不能为空',
            'username.max' => '用户名不能超过32个字符',
            'username.min' => '用户名不能小于6个字符',
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
        $login = new UserModel();
        $user['user_pass']   = $data['password'];
        if ($validate::is($data['username'], 'email')) {
            $user['user_email'] = $data['username'];
            $log = $login->doEmail($user);
        } else if (preg_match('/(^(13\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/', $data['username'])) {
            $user['mobile'] = $data['username'];
            $log = $login->doMobile($user);
        } else {
            $user['user_login'] = $data['username'];
            $log = $login->doName($user);
        }
        $session_login_http_referer = session('login_http_referer');
        $redirect                   = empty($session_login_http_referer) ? $this->request->root() : $session_login_http_referer;
        switch ($log){
            case 0:
                $this->success('登录成功',$redirect);
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


    // 前台用户邮箱激活
    public function active()
    {
        $this->check_login();
        $this->display(":active");
    }

    // 前台用户邮箱激活提交
    public function doactive()
    {
        $this->check_login();
        $current_user = session('user');
        if ($current_user['user_status'] == 2) {
            $this->_send_to_active();
            $this->success('激活邮件发送成功，激活请重新登录！', U("user/index/logout"));
        } else if ($current_user['user_status'] == 1) {
            $this->error('您的账号已经激活，无需再次激活！');
        } else {
            $this->error('您的账号无法发送激活邮件！');
        }
    }

    // 前台用户忘记密码
    public function forgotPassword()
    {
        return $this->fetch(":forgot_password");
    }

    // 前台用户忘记密码提交(邮件方式找回)
    public function doforgot_password()
    {
        if ($this->request->isPost()) {
            if (!sp_check_verify_code()) {
                $this->error("验证码错误！");
            } else {
                $users_model = M("Users");
                $rules       = [
                    //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
                    ['email', 'require', '邮箱不能为空！', 1],
                    ['email', 'email', '邮箱格式不正确！', 1], // 验证email字段格式是否正确

                ];
                if ($users_model->validate($rules)->create() === false) {
                    $this->error($users_model->getError());
                } else {
                    $email     = I("post.email");
                    $find_user = $users_model->where(["user_email" => $email])->find();
                    if ($find_user) {
                        $this->_send_to_resetpass($find_user);
                        $this->success("密码重置邮件发送成功！", __ROOT__ . "/");
                    } else {
                        $this->error("账号不存在！");
                    }

                }

            }

        }
    }

    // 前台用户忘记密码提交(手机方式找回)
    public function do_mobile_forgot_password()
    {
        if ($this->request->isPost()) {

            if (!sp_check_verify_code()) {
                $this->error("验证码错误！");
            }

            $rules = [
                //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
                ['mobile', 'require', '手机号不能为空！', 1],
                ['password', 'require', '密码不能为空！', 1],
                ['password', '5,20', "密码长度至少5位，最多20位！", 1, 'length', 3],
            ];

            $users_model = M("Users");

            if ($users_model->validate($rules)->create() === false) {
                $this->error($users_model->getError());
            }

            if (!sp_check_mobile_verify_code()) {
                $this->error("手机验证码错误！");
            }

            $password = I('post.password');
            $mobile   = I('post.mobile');

            $where['mobile'] = $mobile;

            $users_model = M("Users");
            $result      = $users_model->where($where)->count();
            if ($result) {
                $result = $users_model->where($where)->save(['user_pass' => sp_password($password)]);
                if ($result !== false) {
                    $this->success("密码重置成功！");
                } else {
                    $this->error("密码重置失败！");
                }
            } else {
                $this->error('该手机号未注册！');
            }
        }
    }

    /**
     * 发送密码重置邮件
     * @param array $user
     */
    protected function _send_to_resetpass($user)
    {
        $options = get_site_options();
        //邮件标题
        $title    = $options['site_name'] . "密码重置";
        $uid      = $user['id'];
        $username = $user['user_login'];

        $activekey   = md5($uid . time() . uniqid());
        $users_model = M("Users");

        $result = $users_model->where(["id" => $uid])->save(["user_activation_key" => $activekey]);
        if (!$result) {
            $this->error('密码重置激活码生成失败！');
        }
        //生成激活链接
        $url = U('user/login/password_reset', ["hash" => $activekey], "", true);
        //邮件内容
        $template = <<<hello
		#username#，你好！<br>
		请点击或复制下面链接进行密码重置：<br>
		<a href="http://#link#">http://#link#</a>
hello;
        $content  = str_replace(['http://#link#', '#username#'], [$url, $username], $template);

        $send_result = sp_send_email($user['user_email'], $title, $content);

        if ($send_result['error']) {
            $this->error('密码重置邮件发送失败！');
        }
    }

    // 前台密码重置
    public function password_reset()
    {
        $users_model = M("Users");
        $hash        = I("get.hash");
        $find_user   = $users_model->where(["user_activation_key" => $hash])->find();
        if (empty($find_user)) {
            $this->error('重置码无效！', __ROOT__ . "/");
        } else {
            $this->display(":password_reset");
        }
    }

    // 前台密码重置提交
    public function dopassword_reset()
    {
        if ($this->request->isPost()) {
            if (!sp_check_verify_code()) {
                $this->error("验证码错误！");
            } else {
                $users_model = M("Users");
                $rules       = [
                    //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
                    ['password', 'require', '密码不能为空！', 1],
                    ['password', '5,20', "密码长度至少5位，最多20位！", 1, 'length', 3],
                    ['repassword', 'require', '重复密码不能为空！', 1],
                    ['repassword', 'password', '确认密码不正确', 0, 'confirm'],
                    ['hash', 'require', '重复密码激活码不能空！', 1],
                ];
                if ($users_model->validate($rules)->create() === false) {
                    $this->error($users_model->getError());
                } else {
                    $password = sp_password(I("post.password"));
                    $hash     = I("post.hash");
                    $result   = $users_model->where(["user_activation_key" => $hash])->save(["user_pass" => $password, "user_activation_key" => ""]);
                    if ($result) {
                        $this->success("密码重置成功，请登录！", U("user/login/index"));
                    } else {
                        $this->error("密码重置失败，重置码无效！");
                    }

                }

            }
        }
    }
}