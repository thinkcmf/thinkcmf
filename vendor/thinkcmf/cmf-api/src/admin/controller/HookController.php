<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\logic\HookLogic;
use app\admin\model\HookModel;
use app\admin\model\HookPluginModel;
use app\admin\model\PluginModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class HookController extends RestAdminBaseController
{
    /**
     * 钩子列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/hooks",
     *     summary="钩子列表",
     *     description="钩子列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 2,"type": 1,"once": 0,"name": "应用开始","hook": "app_begin","app": "cmf","description": "应用开始"}
     *              },
     *              "total":1
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
        $hookModel = new HookModel();
        $hooks     = $hookModel->select();
        $this->success("success", ['list' => $hooks, 'total' => $hooks->count()]);

    }

    /**
     * 钩子插件列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/hooks/{hook}/plugins",
     *     summary="钩子插件列表",
     *     description="钩子插件列表",
     *     @OA\Parameter(
     *         name="hook",
     *         in="path",
     *         example="admin_dashboard",
     *         description="钩子,如admin_dashboard",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id":39,"type":1,"has_admin":1,"status":1,"create_time":0,"name":"Swagger","title":"Swagger","demo_url":"http://demo.thinkcmf.com","hooks":"","author":"ThinkCMF","author_url":"http://www.thinkcmf.com","version":"2.0.0","description":"Swagger4.0支持PHP版本>=8.1,同时支持Attributes和Annotations","config":{}}
     *              },
     *              "total":1
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function plugins()
    {
        $hook        = $this->request->param('hook');
        $pluginModel = new PluginModel();
        $plugins     = $pluginModel
            ->field('a.*,b.hook,b.plugin,b.list_order,b.status as hook_plugin_status,b.id as hook_plugin_id')
            ->alias('a')
            ->join('hook_plugin b', 'a.name = b.plugin')
            ->where('b.hook', $hook)
            ->order('b.list_order asc')
            ->select();
        $this->success('success', ['list' => $plugins, 'total' => count($plugins)]);
    }

    /**
     * 钩子插件排序
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/hooks/plugins/list/order",
     *     summary="钩子插件排序",
     *     description="钩子插件排序",
     *     @OA\RequestBody(
     *         description="请求参数<b>注意这里的ID是指表hook_plugin的主键ID</b>",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/ListOrdersRequestForm")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/ListOrdersRequest")
     *         ),
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "幻灯片页面不存在！","data":""})
     *     ),
     * )
     */
    public function pluginListOrder()
    {
        $hookPluginModel = new HookPluginModel();
        parent::listOrders($hookPluginModel);

        $this->success(lang('Sort update successful'));
    }

    /**
     * 同步钩子
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/hooks/sync",
     *     summary="同步钩子",
     *     description="同步钩子",
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

        array_push($apps, 'cmf', 'admin', 'user', 'swoole');

        foreach ($apps as $app) {
            HookLogic::importHooks($app);
        }

        $this->success('同步成功！');
    }


}
