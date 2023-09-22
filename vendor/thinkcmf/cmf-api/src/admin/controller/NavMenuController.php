<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\NavMenuModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class NavMenuController extends RestAdminBaseController
{
    /**
     * 导航菜单列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/nav/menus",
     *     summary="导航菜单列表",
     *     description="导航菜单列表",
     *     @OA\Parameter(
     *         name="nav_id",
     *         in="query",
     *         description="导航id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 1,"nav_id": 1,"parent_id": 0,"status": 1,"list_order": 0,"name": "首页","target": "","href": "home","icon": "","path": "0-1"}
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
        $intNavId     = $this->request->param("nav_id", 0, 'intval');
        $navMenuModel = new NavMenuModel();

        if (empty($intNavId)) {
            $this->error("请指定导航!");
        }

        $objResult = $navMenuModel->where("nav_id", $intNavId)->order(["list_order" => "ASC"])->select();

        $this->success("success", ['list' => $objResult, 'total' => $objResult->count()]);
    }

    /**
     * 添加导航菜单
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/nav/menus",
     *     summary="添加导航菜单",
     *     description="添加导航菜单",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminNavMenuSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminNavMenuSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item": {"id": 1,"nav_id": 1,"parent_id": 0,"status": 1,"list_order": 0,"name": "首页","target": "","href": "home","icon": "","path": "0-1"}
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
        if ($this->request->isPost()) {
            $navMenuModel = new NavMenuModel();
            $arrData      = $this->request->param();

            if (isset($arrData['external_href'])) {
                $arrData['href'] = htmlspecialchars_decode($arrData['external_href']);
            } else {
                $arrData['href'] = htmlspecialchars_decode($arrData['href']);
                $arrData['href'] = base64_decode($arrData['href']);
            }

            unset($arrData['external_href']);

            $navMenuModel->save($arrData);

            $this->success(lang('ADD_SUCCESS'), ['item' => $navMenuModel]);
        }
    }

    /**
     * 获取导航菜单信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/nav/menus/{id}",
     *     summary="获取导航菜单信息",
     *     description="获取导航菜单信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="导航菜单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"nav_id": 1,"parent_id": 0,"status": 1,"list_order": 0,"name": "首页","target": "","href": "home","icon": "","path": "0-1"}
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
        $navMenuModel = new NavMenuModel();

        $intId  = $this->request->param('id', 0, 'intval');
        $objNav = $navMenuModel->where('id', $intId)->find();
        $arrNav = $objNav ? $objNav->toArray() : [];

        $arrNav['href_old'] = $arrNav['href'];

        if (strpos($arrNav['href'], '{') === 0 || $arrNav['href'] == 'home') {
            $arrNav['href'] = base64_encode($arrNav['href']);
        }

        if (empty($arrNav)) {
            $this->error('not found!');
        } else {
            $this->success('success', ['item' => $arrNav]);
        }
    }

    /**
     * 编辑导航菜单
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/nav/menus/{id}",
     *     summary="编辑导航菜单",
     *     description="编辑导航菜单",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="导航菜单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminNavMenuSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminNavMenuSaveRequest")
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

        $intId   = $this->request->param('id', 0, 'intval');
        $arrData = $this->request->param();

        $navMenu = NavMenuModel::find($intId);

        if (isset($arrData['external_href'])) {
            $arrData['href'] = htmlspecialchars_decode($arrData['external_href']);
        } else {
            $arrData['href'] = htmlspecialchars_decode($arrData['href']);
            $arrData['href'] = base64_decode($arrData['href']);
        }

        unset($arrData['external_href']);

        $navMenu->save($arrData);

        $this->success(lang('EDIT_SUCCESS'));
    }

    /**
     * 删除导航菜单
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/nav/menus/{id}",
     *     summary="删除导航菜单",
     *     description="删除导航菜单",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="导航菜单id",
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
        $navMenuModel = new NavMenuModel();

        $intId    = $this->request->param('id', 0, "intval");
        $intNavId = $this->request->param('nav_id', 0, "intval");

        if (empty($intId)) {
            $this->error(lang('NO_ID'));
        }

        $count = $navMenuModel->where('parent_id', $intId)->count();
        if ($count > 0) {
            $this->error('该菜单下还有子菜单，无法删除！');
        }

        $navMenuModel->where('id', $intId)->delete();
        $this->success(lang('DELETE_SUCCESS'));
    }

    /**
     * 切换导航菜单显示状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/nav/menus/{id}/toggle",
     *     summary="切换导航菜单显示状态",
     *     description="切换导航菜单显示状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="导航菜单id",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "导航菜单不存在！","data":""})
     *     ),
     * )
     */
    public function toggle()
    {
        $id      = $this->request->param('id', 0, 'intval');
        $navMenu = NavMenuModel::find($id);
        if (empty($navMenu)) {
            $this->error('导航菜单不存在！');
        } else {
            $status = empty($navMenu['status']) ? 1 : 0;
            $navMenu->save(['status' => $status]);
            $this->success('操作成功！');
        }
    }

    /**
     * 设置导航菜单显示状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/nav/menus/{id}/status/{status}",
     *     summary="设置导航菜单显示状态",
     *     description="设置导航菜单显示状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="导航菜单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="导航菜单显示状态,0:隐藏;1:显示",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "导航菜单不存在！","data":""})
     *     ),
     * )
     */
    public function status()
    {
        $id      = $this->request->param('id', 0, 'intval');
        $status  = $this->request->param('status', 1, 'intval');
        $navMenu = NavMenuModel::find($id);
        if (empty($navMenu)) {
            $this->error('导航菜单不存在！');
        } else {
            $status = empty($status) ? 0 : 1;
            $navMenu->save(['status' => $status]);
            $this->success('操作成功！');
        }
    }

    /**
     * 导航菜单排序
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/nav/menus/list/order",
     *     summary="导航菜单排序",
     *     description="导航菜单排序",
     *     @OA\RequestBody(
     *         description="<b>请求参数</b>",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "导航菜单不存在！","data":""})
     *     ),
     * )
     */
    public function listOrder()
    {
        $navMenuModel = new NavMenuModel();
        parent::listOrders($navMenuModel);
        $this->success(lang('Sort update successful'));
    }

}
