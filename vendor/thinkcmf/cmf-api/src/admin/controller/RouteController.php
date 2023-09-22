<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class RouteController extends RestAdminBaseController
{
    /**
     * 路由列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/routes",
     *     summary="路由列表",
     *     description="路由列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 1,"list_order": 10000,"status": 1,"type": 1,
     *                      "full_url": "demo/List/index","url": "list/:id"
     *                  }
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
        global $CMF_GV_routes;
        $routeModel = new RouteModel();
        $routes     = RouteModel::order("list_order asc")->select();
        $routeModel->getRoutes(true);
        unset($CMF_GV_routes);
        $this->success("success", ['list' => $routes, 'total' => count($routes)]);
    }

    /**
     * 添加路由
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/routes",
     *     summary="添加路由",
     *     description="添加路由",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminRouteSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminRouteSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 7,"list_order": 5000,"status": 1,"type": 2,"full_url": "portal/Page/index?id=3","url": "contact$"}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function save()
    {
        $data       = $this->request->param();
        $routeModel = new RouteModel();
        $result     = $this->validate($data, 'Route');
        if ($result !== true) {
            $this->error($result);
        }
        $routeModel->save($data);
        $this->success(lang('ADD_SUCCESS'), ['item' => $routeModel]);
    }

    /**
     * 获取路由信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/routes/{id}",
     *     summary="获取路由信息",
     *     description="获取路由信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="路由id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 7,"list_order": 5000,"status": 1,"type": 2,"full_url": "portal/Page/index?id=3","url": "contact$"}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function read($id)
    {
        $id    = $this->request->param("id", 0, 'intval');
        $route = RouteModel::find($id);

        if (empty($route)) {
            $this->error('not found!');
        } else {
            $this->success('success', ['item' => $route]);
        }
    }

    /**
     * 编辑路由
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/routes/{id}",
     *     summary="编辑路由",
     *     description="编辑路由",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="路由id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminRouteSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminRouteSaveRequest")
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
    public function update($id)
    {
        $data   = $this->request->param();
        $result = $this->validate($data, 'Route');
        if ($result !== true) {
            $this->error($result);
        }
        $route = RouteModel::find($data['id']);
        if (empty($route)) {
            $this->error('路由未找到!');
        }
        $route->save($data);
        $this->success(lang('EDIT_SUCCESS'));
    }

    /**
     * 删除路由
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/routes/{id}",
     *     summary="删除路由",
     *     description="删除路由",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="路由id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "删除成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error","data":""})
     *     ),
     * )
     */
    public function delete($id)
    {
        $id = $this->request->param('id', 0, 'intval');
        RouteModel::destroy($id);

        $this->success(lang('DELETE_SUCCESS'));
    }

    /**
     * 切换路由显示状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/routes/{id}/toggle",
     *     summary="切换路由显示状态",
     *     description="切换路由显示状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="路由id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "路由不存在！","data":""})
     *     ),
     * )
     */
    public function toggle()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $route = RouteModel::find($id);
        if (empty($route)) {
            $this->error('路由不存在！');
        } else {
            $status = empty($route['status']) ? 1 : 0;
            $route->save(['status' => $status]);
            $this->success('操作成功！');
        }
    }

    /**
     * 设置路由显示状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/routes/{id}/status/{status}",
     *     summary="设置路由显示状态",
     *     description="设置路由显示状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="路由id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="路由显示状态,0:隐藏;1:显示",
     *         example="1",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "路由不存在！","data":""})
     *     ),
     * )
     */
    public function status()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $status = $this->request->param('status', 1, 'intval');
        $route  = RouteModel::find($id);
        if (empty($route)) {
            $this->error('路由不存在！');
        } else {
            $status = empty($status) ? 0 : 1;
            $route->save(['status' => $status]);
            $this->success('操作成功！');
        }
    }

    /**
     * 路由排序
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/routes/list/order",
     *     summary="路由排序",
     *     description="路由排序",
     *     @OA\RequestBody(
     *         description="请求参数",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "路由不存在！","data":""})
     *     ),
     * )
     */
    public function listOrder()
    {
        $routeModel = new RouteModel();
        parent::listOrders($routeModel);
        $this->success(lang('Sort update successful'));
    }

    /**
     * 应用路由规则列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/routes/app/urls",
     *     summary="应用路由规则列表",
     *     description="应用路由规则列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  "user/Login/index":{"name":"用户登录","vars":{},"simple":false,"action":"user/Login/index","suggest_url":"login$"}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function appUrls()
    {
        $routeModel = new RouteModel();
        $urls       = $routeModel->getAppUrls();

        foreach ($urls as $key => $url) {
            $urls[$key]['suggest_url'] = $this->_suggest_url($url);
        }

        $this->success("success", ['list' => $urls,'total'=>count($urls)]);
    }

    private function _suggest_url($url)
    {
        $actionArr = explode('/', $url['action']);

        $params = array_keys($url['vars']);

        $urlDepr1Params = [];

        $urlDepr2Params = [];

        if (!empty($params)) {

            foreach ($params as $param) {
                if (empty($url['vars'][$param]['require'])) {
                    array_push($urlDepr1Params, "[:$param]");
                } else {
                    array_push($urlDepr1Params, ":$param");
                }

                array_push($urlDepr2Params, htmlspecialchars('<') . $param . htmlspecialchars('>'));
            }

        }

        if ($actionArr[2] == 'index') {
            $actionArr[1] = cmf_parse_name($actionArr[1]);
            return empty($params) ? $actionArr[1] . '$' : ($actionArr[1] . '/' . implode('/', $urlDepr1Params) /*. '或' . $actionArr[1] . '-' . implode('-', $urlDepr2Params)*/);
        } else {
            $actionArr[2] = cmf_parse_name($actionArr[2]);
            return empty($params) ? $actionArr[2] . '$' : ($actionArr[2] . '/' . implode('/', $urlDepr1Params) /*. '或' . $actionArr[2] . '-' . implode('-', $urlDepr2Params)*/);
        }
    }

}
