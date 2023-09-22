<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use cmf\controller\RestAdminBaseController;
use app\admin\model\LinkModel;

class LinkController extends RestAdminBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];

    /**
     * 友情链接列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/links",
     *     summary="友情链接列表",
     *     description="友情链接列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 1,"status": 1,"rating": 1,"list_order": 8,"description": "thinkcmf官网","url": "http://www.thinkcmf.com","name": "ThinkCMF","image": "default/xxxxx.png","target": "_blank","rel": ""}
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
        $linkModel = new LinkModel();
        $links     = $linkModel->select();
        $this->success('success', ['list' => $links, 'total' => $links->count()]);
    }

    /**
     * 添加友情链接
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/links",
     *     summary="添加友情链接",
     *     description="添加友情链接",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminLinkSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminLinkSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"status": 1,"rating": 1,"list_order": 8,"description": "thinkcmf官网",
     *                      "url": "http://www.thinkcmf.com","name": "ThinkCMF","image": "default/xxxxx.png",
     *                          "target": "_blank","rel": ""
     *                     }
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
            $data      = $this->request->param();
            $linkModel = new LinkModel();
            $result    = $this->validate($data, 'Link');
            if ($result !== true) {
                $this->error($result);
            }
            $linkModel->save($data);

            $this->success(lang('ADD_SUCCESS'), ['item' => $linkModel]);
        }
    }

    /**
     * 获取友情链接信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/links/{id}",
     *     summary="获取友情链接信息",
     *     description="获取友情链接信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="友情链接id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"status": 1,"rating": 1,"list_order": 8,"description": "thinkcmf官网",
     *                      "url": "http://www.thinkcmf.com","name": "ThinkCMF","image": "default/xxxxx.png",
     *                          "target": "_blank","rel": ""
     *                     }
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
        $id   = $this->request->param('id', 0, 'intval');
        $link = LinkModel::find($id);

        if (empty($link)) {
            $this->error('not found!');
        } else {
            $this->success('success', ['item' => $link]);
        }

    }

    /**
     * 编辑友情链接
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/links/{id}",
     *     summary="编辑友情链接",
     *     description="编辑友情链接",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="友情链接id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminLinkSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminLinkSaveRequest")
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
        if ($this->request->isPut()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'Link');
            if ($result !== true) {
                $this->error($result);
            }
            $linkModel = LinkModel::find($data['id']);
            $linkModel->save($data);

            $this->success(lang('EDIT_SUCCESS'));
        }
    }

    /**
     * 删除友情链接
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/links/{id}",
     *     summary="删除友情链接",
     *     description="删除友情链接",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="友情链接id",
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
        if ($this->request->isDelete()) {
            $id = $this->request->param('id', 0, 'intval');
            LinkModel::destroy($id);
            $this->success(lang('DELETE_SUCCESS'));
        }
    }

    /**
     * 设置友情链接显示状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/links/{id}/status/{status}",
     *     summary="设置友情链接显示状态",
     *     description="设置友情链接显示状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="友情链接id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="友情链接显示状态,0:隐藏;1:显示",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "友情链接不存在！","data":""})
     *     ),
     * )
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/links/status/{status}",
     *     summary="批量设置友情链接显示状态",
     *     description="批量设置友情链接显示状态",
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="友情链接显示状态,0:隐藏;1:显示",
     *         example="1",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/IdsRequestForm")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/IdsRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "友情链接不存在！","data":""})
     *     ),
     * )
     */
    public function status()
    {
        $status = $this->request->param('status', 1, 'intval');
        $status = empty($status) ? 0 : 1;
        $id     = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $link = LinkModel::find($id);
            if (empty($link)) {
                $this->error('友情链接不存在！');
            } else {
                $link->save(['status' => $status]);
                $this->success(lang('Updated successfully'));
            }
        } else {
            $ids = $this->request->param('ids/a');
            if (!empty($ids)) {
                LinkModel::where('id', 'in', $ids)->update(['status' => $status]);
                $this->success(lang('Updated successfully'));
            } else {
                $this->error('参数错误！');
            }
        }

    }

    /**
     * 友情链接排序
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/links/list/order",
     *     summary="友情链接排序",
     *     description="友情链接排序",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "友情链接不存在！","data":""})
     *     ),
     * )
     */
    public function listOrder()
    {
        $linkModel = new  LinkModel();
        parent::listOrders($linkModel);
        $this->success(lang('Sort update successful'));
    }


}
