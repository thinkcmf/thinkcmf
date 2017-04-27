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
use think\Validate;


class BangController extends UserBaseController
{

    /**
     * 绑定账户资料
     */
    public function index()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch("profile/bang");
    }

    public function mobile()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'code' => 'require',
            ]);
            $validate->message([
                'code.require' => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $errMsg = cmf_check_verification_code($data['mobile'],$data['code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }
            $register = new UserModel();
            $user['mobile']   = $data['mobile'];
            $log = $register->bangMobile($user);
            switch ($log){
                case 0:
                    $this->success('手机号绑定成功');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }

    public function email()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'code' => 'require',
            ]);
            $validate->message([
                'code.require' => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $errMsg = cmf_check_verification_code($data['mobile'],$data['code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }
            $register = new UserModel();
            $user['user_email']   = $data['user_email'];
            $log = $register->bangEmail($user);
            switch ($log){
                case 0:
                    $this->success('电子邮箱绑定成功');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }

}