<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use cmf\controller\RestBaseController;
use think\facade\Db;
use think\facade\Validate;
use OpenApi\Annotations as OA;

class PublicController extends RestBaseController
{

    /**
     * 后台管理员登录
     * @throws \think\exception\DbException
     * @OA\Tag(
     *     name="admin",
     *     description="系统核心后台管理"
     * )
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/public/login",
     *     summary="后台管理员登录",
     *     description="后台管理员登录(请先使用原来登录页面登录，登录获取token后再使用后台API)",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/LoginRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/LoginRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response="1",
     *         description="登录成功",
     *         @OA\JsonContent(ref="#/components/schemas/AdminPublicLoginResponse")
     *     ),
     *     @OA\Response(response="0",ref="#/components/responses/0"),
     * )
     */
    public function login()
    {
        $this->error('请先使用原来登录页面登录，登录获取token 后再使用后台API');
        // TODO 增加最后登录信息记录,如 ip
        $validate = new \think\Validate();
        $validate->rule([
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

        if (empty($this->deviceType) && (empty($data['device_type']) || !in_array($data['device_type'], $this->allowedDeviceTypes))) {
            $this->error("请求错误,未知设备!");
        } else if (!empty($data['device_type'])) {
            $this->deviceType = $data['device_type'];
        }

        $userTokenQuery = Db::name("user_token")
            ->where('user_id', $findUser['id'])
            ->where('device_type', $this->deviceType);
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
                'device_type' => $this->deviceType
            ]);
        } else {
            $result = Db::name("user_token")
                ->where('user_id', $findUser['id'])
                ->where('device_type', $this->deviceType)
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

    /**
     * 后台管理员退出
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/public/logout",
     *     summary="后台管理员退出",
     *     description="后台管理员退出",
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "退出成功!","data": null})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "退出失败!","data": null})
     *     ),
     * )
     */
    public function logout()
    {
        $userId = $this->getUserId();
        Db::name('user_token')->where([
            'token'       => $this->token,
            'user_id'     => $userId,
            'device_type' => $this->deviceType
        ])->update(['token' => '']);

        session('ADMIN_ID', null);
        $this->success("退出成功!");
    }

}
