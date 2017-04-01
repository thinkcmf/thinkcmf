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
use app\user\model\UserModel;
use think\Validate;

class PublicController extends HomeBaseController
{

    // 用户头像api
    public function avatar()
    {
        $id   = $this->request->param("id", 0, "intval");
        $user = UserModel::get($id);

        $avatar='';
        if (!empty($user)) {
            $avatar              = cmf_get_user_avatar_url($user['avatar']);
        }

        if (empty($avatar)) {
            $avatar = request()->root() . "/static/images/headicon.png";
        }

        return $this->redirect($avatar);
    }

    /**
     * 验证码发送
     */
    public function sendCode()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $code = cmf_get_verification_code($data['username']);
            if (empty($code)) {
                $this->error("验证码发送过多,请明天再试!");
            }
            $validate = new Validate();
            $code     = rand(100000, 999999);
            if ($validate->check($data['username'], ['email'])) {

                cmf_verification_code_log($data['username'], $code);

            } else if (preg_match('/(^(13\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/', $data['username'])) {

                cmf_verification_code_log($data['username'], $code);
            }
            $this->success("验证码已经发送成功!");
        } else {
            $this->error("请求错误");
        }
    }
}
