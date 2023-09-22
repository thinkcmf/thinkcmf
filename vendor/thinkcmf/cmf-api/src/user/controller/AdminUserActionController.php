<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace api\user\controller;

use app\user\logic\UserActionLogic;
use app\user\model\UserActionModel;
use cmf\controller\RestAdminBaseController;

/**
 * Class AdminUserActionController
 * @package app\user\controller
 */
class AdminUserActionController extends RestAdminBaseController
{
    /**
     * 用户操作列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"user"},
     *     path="/admin/user/actions",
     *     summary="用户操作列表",
     *     description="用户操作列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 1,"score": 1,"coin": 1,"reward_number": 1,"cycle_type": 2,"cycle_time": 1,"name": "用户登录","action": "login","app": "user","url": ""}
     *              },"total":1
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function index()
    {
        $actions = UserActionModel::select();
        $this->success('success', ['list' => $actions, 'total' => $actions->count()]);
    }

    /**
     * 获取用户操作
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"user"},
     *     path="/admin/user/actions/{id}",
     *     summary="获取用户操作",
     *     description="获取用户操作",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="用户操作ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{
     *                  "id": 1,"score": 1,"coin": 1,"reward_number": 1,"cycle_type": 2,"cycle_time": 1,"name": "用户登录","action": "login","app": "user","url": ""
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function read()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $action = UserActionModel::where('id', $id)->find();

        if (empty($action)) {
            $this->error('未找到！');
        }
        $this->success('success', ['item' => $action]);
    }

    /**
     * 编辑用户操作
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"user"},
     *     path="/admin/user/actions/{id}",
     *     summary="编辑用户操作",
     *     description="编辑用户操作",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="用户操作id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/UserAdminUserActionSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UserAdminUserActionSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function update()
    {
        if ($this->request->isPut()) {
            $id = $this->request->param('id', 0, 'intval');

            $data = $this->request->param();

            UserActionModel::where('id', $id)
                ->strict(false)
                ->field('score,coin,reward_number,cycle_type,cycle_time')
                ->update($data);

            $this->success(lang('EDIT_SUCCESS'));
        }
    }

    /**
     * 同步用户操作
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"user"},
     *     path="/admin/user/actions/sync",
     *     summary="同步用户操作",
     *     description="同步用户操作",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "同步成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error！","data":""})
     *     ),
     * )
     */
    public function sync()
    {
        $apps = cmf_scan_dir($this->app->getAppPath() . '*', GLOB_ONLYDIR);

        array_push($apps, 'admin', 'user');

        foreach ($apps as $app) {
            UserActionLogic::importUserActions($app);
        }

        $this->success("同步成功!");
    }


}
