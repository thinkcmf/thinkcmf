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
use app\admin\service\AdminMenuService;
use cmf\controller\RestAdminBaseController;
use cmf\controller\RestBaseController;
use OpenApi\Annotations as OA;
use think\facade\Db;
use think\facade\Validate;

class SlideItemController extends RestAdminBaseController
{
    /**
     * 幻灯片页面列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/slide_items",
     *     summary="幻灯片页面列表",
     *     description="幻灯片页面列表",
     *     @OA\Parameter(
     *         name="slide_id",
     *         in="query",
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
     *              "slide_items":{
     *                  {"id": 1,"slide_id": 1,"status": 1,"list_order": 10000,"title": "testtest","image": "","url": "","target": "_blank","description": "","content": "test","more": null}
     *              }
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
        $slideId    = $this->request->param('slide_id', 0, 'intval');
        $slideItems = SlideItemModel::where('slide_id', $slideId)->select();
        $this->success("success", ['slide_items' => $slideItems]);
    }

    /**
     * 添加幻灯片页面
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/slide_items",
     *     summary="添加幻灯片页面",
     *     description="添加幻灯片页面",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideItemSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideItemSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "slide_item": {
     *                      "slide_id": "1",
     *                      "title": "这里是标题",
     *                      "url": "https://www.thinkcmf.com","target": "_blank",
     *                      "image": "default/xxxx.jpg",
     *                      "description": "这里是描述",
     *                      "content": "这里是内容","id": 2
     *              }
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
        $data      = $this->request->param('', null, 'strip_tags');
        $slideItem = SlideItemModel::create($data);
        $this->success(lang('ADD_SUCCESS'), ['slide_item' => $slideItem]);
    }

    /**
     * 获取幻灯片页面信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/slide_items/{id}",
     *     summary="获取幻灯片页面信息",
     *     description="获取幻灯片页面信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="幻灯片页面id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "slide_item":{"id": 1,"slide_id": 1,"status": 1,"list_order": 10000,"title": "testtest","image": "default/20230816/93ce77f764658f4020ada1acc398fc6a.png","url": "","target": "_blank","description": "","content": "test","more": null}
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
        $id     = $this->request->param('id', 0, 'intval');
        $result = SlideItemModel::where('id', $id)->find();

        if (empty($result)) {
            $this->error('not found!');
        } else {
            $this->success('success', ['slide_item' => $result]);
        }
    }

    /**
     * 编辑幻灯片页面
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/slide_items/{id}",
     *     summary="编辑幻灯片页面",
     *     description="编辑幻灯片页面",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="幻灯片页面id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideItemSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminSlideItemSaveRequest")
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
        $data = $this->request->param('', null, 'strip_tags');

        $data['image'] = cmf_asset_relative_url($data['image']);

        SlideItemModel::update($data);
        $this->success(lang('EDIT_SUCCESS'));
    }

    /**
     * 删除幻灯片页面
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/slide_items/{id}",
     *     summary="删除幻灯片页面",
     *     description="删除幻灯片页面",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="幻灯片页面id",
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
        $id        = $this->request->param('id', 0, 'intval');
        $slideItem = SlideItemModel::find($id);
        $result    = SlideItemModel::destroy($id);
        if ($result) {
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }

}
