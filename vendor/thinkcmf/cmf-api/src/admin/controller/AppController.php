<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\logic\AppLogic;
use app\admin\model\UserModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class AppController extends RestAdminBaseController
{
    /**
     * 应用列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/apps",
     *     summary="应用列表",
     *     description="应用列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {
     *                      "title": "演示应用",
     *                      "name": "demo",
     *                      "version": "1.0.3",
     *                      "demo_url": "http://demo.thinkcmf.com",
     *                      "author": "ThinkCMF",
     *                      "author_url": "http://www.thinkcmf.com",
     *                      "keywords": "ThinkCMF 演示应用",
     *                      "description": "ThinkCMF 演示应用",
     *                      "config_url": "",
     *                      "installed": 1,
     *                      "local_verison": "1.0.3"
     *                  }
     *              },
     *             "total":1
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
        $apps = AppLogic::getList();
        $this->success("success", ['list' => $apps, 'total' => count($apps)]);
    }


    /**
     * 安装应用
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/apps/{name}",
     *     summary="安装应用",
     *     description="安装应用",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         example="demo",
     *         description="应用名,如 demo,portal",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "安装成功！","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $appName = $this->request->param('name', '', 'trim');
            $result  = AppLogic::install($appName);

            if ($result !== true) {
                $this->error($result);
            }

            $this->success(lang('Installed successfully'));
        }
    }

    /**
     * 更新应用
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/apps/{name}",
     *     summary="更新应用",
     *     description="更新应用",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         example="demo",
     *         description="应用名,如 demo,portal",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "更新成功！","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function update()
    {
        $appName = $this->request->param('name', '', 'trim');
        $result  = AppLogic::update($appName);

        if ($result !== true) {
            $this->error($result);
        }
        $this->success(lang('Updated successfully'));
    }

    /**
     *  卸载应用
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/apps",
     *     summary="卸载应用",
     *     description="卸载应用",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminAppUninstallDeleteRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminAppUninstallDeleteRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "卸载成功！","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "此应用无法通过网页卸载，请使用命令行程序卸载!","data":""})
     *     ),
     * )
     */
    public function uninstall()
    {
        $appName        = $this->request->param('name', '', 'trim');
        $confirmAppName = $this->request->param('confirm_name', '', 'trim');
        $allowedApps    = ['demo', 'portal'];

        if (empty($appName) || empty($confirmAppName)) {
            $this->error('请输入应用名！');
        }

        if ($appName != $confirmAppName) {
            $this->error('应用名输入不一致！');
        }

        if (!in_array($appName, $allowedApps)) {
            $this->error('此应用无法通过网页卸载，请使用命令行程序卸载！');
        }

        $password = $this->request->param('password', '', 'trim');
        if (empty($password)) {
            $this->error('请输入网站创始人后台登录密码！');
        }

        $passwordInDb = UserModel::where('id', 1)->value('user_pass');
        if (!cmf_compare_password($password, $passwordInDb)) {
            $this->error('网站创始人后台登录密码不正确！');
        }

        $result = AppLogic::uninstall($appName);
        if ($result === true) {
            $this->success(lang('Uninstall successful'));
        } else if ($result === false) {
            $this->error(lang('Uninstall failed'));
        } else {
            $this->error($result);
        }
    }


}
