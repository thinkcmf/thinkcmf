<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\RecycleBinModel;
use app\admin\model\SlideItemModel;
use app\admin\model\SlideModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class SlideController extends RestAdminBaseController
{
    /**
     * 幻灯片列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/slides",
     *     summary="幻灯片列表",
     *     description="幻灯片列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
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
        $slidePostModel = new SlideModel();
        $slides         = $slidePostModel->where('delete_time', 0)->select();
        $this->success("success", ['list' => $slides, 'total' => $slides->count()]);
    }

    /**
     * 添加幻灯片
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/slides",
     *     summary="添加幻灯片",
     *     description="添加幻灯片",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
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
        $data           = $this->request->param('', null, 'strip_tags');
        $slidePostModel = new SlideModel();
        $result         = $this->validate($data, 'Slide');
        if ($result !== true) {
            $this->error($result);
        }
        $slidePostModel->save($data);

        $this->success(lang('ADD_SUCCESS'), ['item' => $slidePostModel]);
    }

    /**
     * 获取幻灯片信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/slides/{id}",
     *     summary="获取幻灯片信息",
     *     description="获取幻灯片信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="幻灯片id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
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
        $id             = $this->request->param('id');
        $slidePostModel = new SlideModel();
        $result         = $slidePostModel->where('id', $id)->find();
        if (empty($result)) {
            $this->error('not found!');
        } else {
            $this->success('success', ['item' => $result]);
        }
    }

    /**
     * 编辑幻灯片
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/slides/{id}",
     *     summary="编辑幻灯片",
     *     description="编辑幻灯片",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="幻灯片id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideSaveRequest")
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
        $data   = $this->request->param('', null, 'strip_tags');
        $result = $this->validate($data, 'Slide');
        if ($result !== true) {
            $this->error($result);
        }
        $slidePostModel = SlideModel::find($data['id']);
        $slidePostModel->save($data);
        $this->success(lang('EDIT_SUCCESS'));
    }

    /**
     * 删除幻灯片
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/slides/{id}",
     *     summary="删除幻灯片",
     *     description="删除幻灯片",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="幻灯片id",
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
        $id             = $this->request->param('id', 0, 'intval');
        $slidePostModel = SlideModel::where('id', $id)->find();
        if (empty($slidePostModel)) {
            $this->error('幻灯片不存在!');
        }

        //如果存在页面。则不能删除。
        $slidePostCount = SlideItemModel::where('slide_id', $id)->count();
        if ($slidePostCount > 0) {
            $this->error('此幻灯片有页面无法删除!');
        }

        $data = [
            'object_id'   => $id,
            'create_time' => time(),
            'table_name'  => 'slide',
            'name'        => $slidePostModel['name']
        ];

        $resultSlide = $slidePostModel->save(['delete_time' => time()]);
        if ($resultSlide) {
            RecycleBinModel::insert($data);
        }
        $this->success(lang('DELETE_SUCCESS'));
    }

}
