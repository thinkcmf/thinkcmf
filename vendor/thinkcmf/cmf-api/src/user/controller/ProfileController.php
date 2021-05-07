<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\user\controller;

use api\user\model\UserModel;
use cmf\controller\RestUserBaseController;
use think\Validate;

class ProfileController extends RestUserBaseController
{
    /**
     * 用户密码修改
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changePassword()
    {
        $validate = new Validate();
        $validate->rule([
            'old_password'     => 'require',
            'password'         => 'require',
            'confirm_password' => 'require|confirm:password'
        ]);
        $validate->message([
            'old_password.require'     => '请输入您的旧密码!',
            'password.require'         => '请输入您的新密码!',
            'confirm_password.require' => '请输入确认密码!',
            'confirm_password.confirm' => '两次输入的密码不一致!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $userId       = $this->getUserId();
        $userPassword = UserModel::where('id', $userId)->value('user_pass');

        if (!cmf_compare_password($data['old_password'], $userPassword)) {
            $this->error('旧密码不正确!');
        }

        UserModel::where('id', $userId)->update(['user_pass' => cmf_password($data['password'])]);

        $this->success("密码修改成功!");

    }

    /**
     * 用户绑定邮箱
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function bindingEmail()
    {
        $validate = new Validate();
        $validate->rule([
            'email'             => 'require|email|unique:user,user_email',
            'verification_code' => 'require'
        ]);
        $validate->message([
            'email.require'             => '请输入您的邮箱!',
            'email.email'               => '请输入正确的邮箱格式!',
            'email.unique'              => '邮箱账号已存在!',
            'verification_code.require' => '请输入数字验证码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $userId    = $this->getUserId();
        $userEmail = UserModel::where('id', $userId)->value('user_email');

        if (!empty($userEmail)) {
            $this->error("您已经绑定邮箱!");
        }

        $errMsg = cmf_check_verification_code($data['email'], $data['verification_code']);
        if (!empty($errMsg)) {
            $this->error($errMsg);
        }

        UserModel::where('id', $userId)->update(['user_email' => $data['email']]);

        $this->success("绑定成功!");
    }

    /**
     * 用户绑定手机号
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function bindingMobile()
    {
        $validate = new Validate();
        $validate->rule([
            'mobile'            => 'require|unique:user,mobile',
            'verification_code' => 'require'
        ]);
        $validate->message([
            'mobile.require'            => '请输入您的手机号!',
            'mobile.unique'             => '手机号已经存在！',
            'verification_code.require' => '请输入数字验证码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        if (!cmf_check_mobile($data['mobile'])) {
            $this->error("请输入正确的手机格式!");
        }


        $userId = $this->getUserId();
        $mobile = UserModel::where('id', $userId)->value('mobile');

        if (!empty($mobile)) {
            $this->error("您已经绑定手机!");
        }

        $errMsg = cmf_check_verification_code($data['mobile'], $data['verification_code']);
        if (!empty($errMsg)) {
            $this->error($errMsg);
        }

        UserModel::where('id', $userId)->update(['mobile' => $data['mobile']]);

        $this->success("绑定成功!");
    }

    /**
     * 用户基本信息获取及修改
     * @param string $field 需要获取的字段名
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function userInfo($field = '')
    {
        //判断请求为GET，获取信息
        if ($this->request->isGet()) {
            $userId   = $this->getUserId();
            $fieldStr = 'user_type,user_login,mobile,user_email,user_nickname,avatar,signature,user_url,sex,birthday,score,coin,user_status,user_activation_key,create_time,last_login_time,last_login_ip';
            if (empty($field)) {
                $userData = UserModel::field($fieldStr)->find($userId);
            } else {
                $fieldArr     = explode(',', $fieldStr);
                $postFieldArr = explode(',', $field);
                $mixedField   = array_intersect($fieldArr, $postFieldArr);
                if (empty($mixedField)) {
                    $this->error('您查询的信息不存在！');
                }
                if (count($mixedField) > 1) {
                    $fieldStr = implode(',', $mixedField);
                    $userData = UserModel::field($fieldStr)->find($userId);
                } else {
                    $userData = UserModel::where('id', $userId)->value($mixedField);
                }
            }
            $this->success('获取成功！', $userData);
        }
        //判断请求为POST,修改信息
        if ($this->request->isPost()) {
            $userId   = $this->getUserId();
            $fieldStr = 'user_nickname,avatar,signature,user_url,sex,birthday';
            $data     = $this->request->post();
            if (empty($data)) {
                $this->error('修改失败，提交表单为空！');
            }

            if (!empty($data['birthday'])) {
                $data['birthday'] = strtotime($data['birthday']);
            }

            $upData = UserModel::where('id', $userId)->field($fieldStr)->update($data);
            if ($upData !== false) {
                $this->success('修改成功！');
            } else {
                $this->error('修改失败！');
            }
        }
    }

}
