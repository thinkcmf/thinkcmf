<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\Db;

class RestAdminBaseController extends RestBaseController
{
    public function _initialize()
    {

        $token      = $this->request->header('XX-Token');
        $deviceType = $this->request->header('XX-Device-Type');

        if (empty($token)) {
            $this->error(['code' => 10001, 'msg' => 'Token不能为空']);
        }

        if (empty($deviceType)) {
            $this->error(['code' => 10001, 'msg' => '设备类型不能为空']);
        }

        if (!in_array($deviceType, $this->allowedDeviceTypes)) {
            $this->error(['code' => 10001, 'msg' => '设备类型不存在!']);
        }

        $this->token      = $token;
        $this->deviceType = $deviceType;

        $user = Db::name('user_token')
            ->alias('a')
            ->field('b.*')
            ->where(['token' => $token, 'device_type' => $deviceType])
            ->join('__USER__ b', 'a.user_id = b.id')
            ->find();

        if (empty($user)) {
            $this->error(['code' => 10001, 'msg' => '登录已失效!']);
        }

        $this->userId = $user['id'];
    }


}