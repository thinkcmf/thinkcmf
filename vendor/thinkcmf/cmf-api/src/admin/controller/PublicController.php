<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\facade\Validate;

class PublicController extends RestBaseController
{

    // 用户登录 TODO 增加最后登录信息记录,如 ip
    public function login()
    {
        $validate = new \think\Validate([
            'username' => 'require',
            'password' => 'require'
        ]);
        $validate->message([
            'username.require' => '请输入手机号,邮箱或用户名!',
            'password.require' => '请输入您的密码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $userQuery = Db::name("user");
        if (Validate::is($data['username'], 'email')) {
            $userQuery = $userQuery->where('user_email', $data['username']);
        } else if (cmf_check_mobile($data['username'])) {
            $userQuery = $userQuery->where('mobile', $data['username']);
        } else {
            $userQuery = $userQuery->where('user_login', $data['username']);
        }

        $findUser = $userQuery->find();

        if (empty($findUser)) {
            $this->error("用户不存在!");
        } else {

            switch ($findUser['user_status']) {
                case 0:
                    $this->error('您已被拉黑!');
                case 2:
                    $this->error('账户还没有验证成功!');
            }

            if (!cmf_compare_password($data['password'], $findUser['user_pass'])) {
                $this->error("密码不正确!");
            }
        }

        $allowedDeviceTypes = ['mobile', 'android', 'iphone', 'ipad', 'web', 'pc', 'mac'];

        if (empty($data['device_type']) || !in_array($data['device_type'], $allowedDeviceTypes)) {
            $this->error("请求错误,未知设备!");
        }

        $userTokenQuery = Db::name("user_token")
            ->where('user_id', $findUser['id'])
            ->where('device_type', $data['device_type']);
        $findUserToken  = $userTokenQuery->find();
        $currentTime    = time();
        $expireTime     = $currentTime + 24 * 3600 * 180;
        $token          = md5(uniqid()) . md5(uniqid());
        if (empty($findUserToken)) {
            $result = Db::name("user_token")->insert([
                'token'       => $token,
                'user_id'     => $findUser['id'],
                'expire_time' => $expireTime,
                'create_time' => $currentTime,
                'device_type' => $data['device_type']
            ]);
        } else {
            $result = Db::name("user_token")
                ->where('user_id', $findUser['id'])
                ->where('device_type', $data['device_type'])
                ->update([
                    'token'       => $token,
                    'expire_time' => $expireTime,
                    'create_time' => $currentTime
                ]);
        }


        if (empty($result)) {
            $this->error("登录失败!");
        }

        $this->success("登录成功!", ['token' => $token]);
    }

    // 管理员退出
    public function logout()
    {
        $userId = $this->getUserId();
        Db::name('user_token')->where([
            'token'       => $this->token,
            'user_id'     => $userId,
            'device_type' => $this->deviceType
        ])->update(['token' => '']);

        $this->success("退出成功!");
    }

}
