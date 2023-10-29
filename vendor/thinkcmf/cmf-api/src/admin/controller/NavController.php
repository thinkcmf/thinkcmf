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
use app\admin\model\RecycleBinModel;
use app\admin\model\NavItemModel;
use app\admin\model\NavModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class NavController extends RestAdminBaseController
{
    /**
     * 导航列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/navs",
     *     summary="导航列表",
     *     description="导航列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 1,"is_main": 1,"name": "主导航","remark": "主导航"}
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
        $navModel = new NavModel();
        $navs     = $navModel->select();
        $this->success("success", ['list' => $navs, 'total' => $navs->count()]);
    }

    /**
     * 添加导航
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/navs",
     *     summary="添加导航",
     *     description="添加导航",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminNavSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminNavSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"is_main": 1,"name": "主导航","remark": "主导航"}
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
            $navModel = new NavModel();
            $arrData  = $this->request->post();

            if (empty($arrData["is_main"])) {
                $arrData["is_main"] = 0;
            } else {
                $navModel->where("is_main", 1)->update(["is_main" => 0]);
            }

            $navModel->save($arrData);
            $this->success(lang("ADD_SUCCESS"), ['item' => $navModel]);
        }
    }

    /**
     * 获取导航信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/navs/{id}",
     *     summary="获取导航信息",
     *     description="获取导航信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
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
     *              "item":{"id": 1,"is_main": 1,"name": "主导航","remark": "主导航"}
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
        $id           = $this->request->param('id');
        $navPostModel = new NavModel();
        $result       = $navPostModel->where('id', $id)->find();
        if (empty($result)) {
            $this->error('not found!');
        } else {
            $this->success('success', ['item' => $result]);
        }
    }

    /**
     * 编辑导航
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/navs/{id}",
     *     summary="编辑导航",
     *     description="编辑导航",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="导航id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminNavSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminNavSaveRequest")
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
        $navModel = new NavModel();
        $arrData  = $this->request->param();

        if (empty($arrData["is_main"])) {
            $arrData["is_main"] = 0;
        } else {
            $navModel->where("is_main", 1)->update(["is_main" => 0]);
        }

        $navModel->where("id", intval($arrData["id"]))->update($arrData);
        $this->success(lang("EDIT_SUCCESS"));
    }

    /**
     * 删除导航
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/navs/{id}",
     *     summary="删除导航",
     *     description="删除导航",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="导航id",
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
        $navModel = new NavModel();
        $intId    = $this->request->param("id", 0, "intval");

        if (empty($intId)) {
            $this->error(lang("NO_ID"));
        }

        $navModel->where("id", $intId)->delete();
        $this->success(lang("DELETE_SUCCESS"));
    }


    /**
     * 获取共享nav模板结构
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/navs/select/navs",
     *     summary="获取共享nav模板结构",
     *     description="获取共享nav模板结构",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 1,"is_main": 1,"name": "主导航","remark": "主导航"}
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
    public function selectNavs(){
        $navMenuModel = new NavMenuModel();
        $navs = array_merge([[
            "name"      => '首页',
            "url"       => '/',
            "rule"      => base64_encode('home'),
            "parent_id" => 0,
            "id"        => 0,
        ]],$navMenuModel->selectNavs());

        $this->success("success", ["navs"=>$navs]);
    }
}
