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
use think\facade\Db;
use think\facade\Validate;
use cmf\controller\RestBaseController;
use OpenApi\Annotations as OA;

class PublicController extends RestBaseController
{
    /**
     * 用户注册
     * @OA\Post(
     *     tags={"user"},
     *     path="/user/public/register",
     *     summary="用户注册",
     *     description="用户注册",
     *     @OA\RequestBody(
     *          required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                  @OA\Property(
     *                      property="username",
     *                      description="手机号，邮箱，账户",
     *                      type="string",
     *                      required=true
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="密码",
     *                      type="string",
     *                      required=true
     *                  ),
     *                  @OA\Property(
     *                      property="verification_code",
     *                      description="数字验证码",
     *                      type="string",
     *                      required=true
     *                  ),
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "注册并激活成功,请登录!","data": null})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "请输入您的密码!","data": null})
     *     ),
     * )
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function register()
    {
        $validate = new \think\Validate();
        $validate->rule([
            'username'          => 'require',
            'password'          => 'require',
            'verification_code' => 'require'
        ]);
        $validate->message([
            'username.require'          => '请输入手机号,邮箱!',
            'password.require'          => '请输入您的密码!',
            'verification_code.require' => '请输入数字验证码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $user = [];

        $findUserWhere = [];

        if (Validate::is($data['username'], 'email')) {
            $user['user_email']          = $data['username'];
            $findUserWhere['user_email'] = $data['username'];
        } else if (cmf_check_mobile($data['username'])) {
            $user['mobile']          = $data['username'];
            $findUserWhere['mobile'] = $data['username'];
        } else {
            $this->error("请输入正确的手机或者邮箱格式!");
        }

        $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
        if (!empty($errMsg)) {
            $this->error($errMsg);
        }

        $findUserCount = UserModel::where($findUserWhere)->count();

        if ($findUserCount > 0) {
            $this->error("此账号已存在!");
        }

        $user['create_time'] = time();
        $user['user_status'] = 1;
        $user['user_type']   = 2;
        $user['user_pass']   = cmf_password($data['password']);

        $result = UserModel::insert($user);


        if (empty($result)) {
            $this->error("注册失败,请重试!");
        }

        $this->success("注册并激活成功,请登录!");

    }

    /**
     * 验证码登录
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function verificationCodeLogin()
    {
        $validate = new \think\Validate();
        $validate->rule([
            'username'          => 'require',
            'verification_code' => 'require'
        ]);
        $validate->message([
            'username.require'          => '请输入手机号,邮箱!',
            'verification_code.require' => '请输入数字验证码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $user = [];

        $findUserWhere = [];

        if (Validate::is($data['username'], 'email')) {
            $user['user_email']          = $data['username'];
            $findUserWhere['user_email'] = $data['username'];
        } else if (cmf_check_mobile($data['username'])) {
            $user['mobile']          = $data['username'];
            $findUserWhere['mobile'] = $data['username'];
        } else {
            $this->error("请输入正确的手机或者邮箱格式!");
        }

        $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
        if (!empty($errMsg)) {
            $this->error($errMsg);
        }

        $findUser = UserModel::where($findUserWhere)->find();

        if (empty($findUser)) {
            $user['create_time'] = time();
            $user['user_status'] = 1;
            $user['user_type']   = 2;

            $userId   = UserModel::insertGetId($user);
            $findUser = UserModel::where('id', $userId)->find();
        } else {
            switch ($findUser['user_status']) {
                case 0:
                    $this->error('您已被拉黑!');
                case 2:
                    $this->error('账户还没有验证成功!');
            }
            $userId = $findUser['id'];
        }


        $allowedDeviceTypes = $this->allowedDeviceTypes;

        if (empty($this->deviceType) && (empty($data['device_type']) || !in_array($data['device_type'], $this->allowedDeviceTypes))) {
            $this->error("请求错误,未知设备!");
        } else if (!empty($data['device_type'])) {
            $this->deviceType = $data['device_type'];
        }

        $findUserToken = Db::name("user_token")
            ->where('user_id', $userId)
            ->where('device_type', $this->deviceType)
            ->find();
        $currentTime   = time();
        $expireTime    = $currentTime + 24 * 3600 * 180;
        $token         = md5(uniqid()) . md5(uniqid());
        if (empty($findUserToken)) {
            $result = Db::name("user_token")->insert([
                'token'       => $token,
                'user_id'     => $userId,
                'expire_time' => $expireTime,
                'create_time' => $currentTime,
                'device_type' => $this->deviceType
            ]);
        } else {
            $result = Db::name("user_token")
                ->where('user_id', $userId)
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

        $this->success("登录成功!", ['token' => $token, 'user' => $findUser]);


    }

    /**
     * 用户登录
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @OA\Post(
     *     tags={"user"},
     *     path="/user/public/login",
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
     *         @OA\JsonContent(ref="#/components/schemas/UserPublicLoginResponse")
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "登录失败!","data": ""})
     *     ),
     * )
     */
    // TODO 增加最后登录信息记录,如 ip
    public function login()
    {
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

        $findUserWhere = [];

        if (Validate::is($data['username'], 'email')) {
            $findUserWhere['user_email'] = $data['username'];
        } else if (cmf_check_mobile($data['username'])) {
            $findUserWhere['mobile'] = $data['username'];
        } else {
            $findUserWhere['user_login'] = $data['username'];
        }

        $findUser = UserModel::where($findUserWhere)->find();

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

        $allowedDeviceTypes = $this->allowedDeviceTypes;

        if (empty($this->deviceType) && (empty($data['device_type']) || !in_array($data['device_type'], $this->allowedDeviceTypes))) {
            $this->error("请求错误,未知设备!");
        } else if (!empty($data['device_type'])) {
            $this->deviceType = $data['device_type'];
        }

//        Db::name("user_token")
//            ->where('user_id', $findUser['id'])
//            ->where('device_type', $data['device_type']);
        $findUserToken = Db::name("user_token")
            ->where('user_id', $findUser['id'])
            ->where('device_type', $this->deviceType)
            ->find();
        $currentTime   = time();
        $expireTime    = $currentTime + 24 * 3600 * 180;
        $token         = md5(uniqid()) . md5(uniqid());
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

        $this->success("登录成功!", ['token' => $token, 'user' => $findUser->hidden([
            'user_pass',
            'user_activation_key',
            'more', 'user_type'
        ])]);
    }

    /**
     * 用户退出
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"user"},
     *     path="/user/public/logout",
     *     summary="用户退出",
     *     description="用户退出",
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

        $this->success("退出成功!");
    }

    /**
     * 用户密码重置
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function passwordReset()
    {
        $validate = new \think\Validate();
        $validate->rule([
            'username'          => 'require',
            'password'          => 'require',
            'verification_code' => 'require'
        ]);
        $validate->message([
            'username.require'          => '请输入手机号,邮箱!',
            'password.require'          => '请输入您的密码!',
            'verification_code.require' => '请输入数字验证码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $userWhere = [];
        if (Validate::is($data['username'], 'email')) {
            $userWhere['user_email'] = $data['username'];
        } else if (cmf_check_mobile($data['username'])) {
            $userWhere['mobile'] = $data['username'];
        } else {
            $this->error("请输入正确的手机或者邮箱格式!");
        }

        $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
        if (!empty($errMsg)) {
            $this->error($errMsg);
        }

        $userPass = cmf_password($data['password']);
        UserModel::where($userWhere)->update(['user_pass' => $userPass]);

        $this->success("密码重置成功,请使用新密码登录!");

    }
}
