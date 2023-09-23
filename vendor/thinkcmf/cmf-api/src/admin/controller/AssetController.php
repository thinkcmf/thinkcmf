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

use app\user\model\AssetModel;
use cmf\controller\RestAdminBaseController;

class AssetController extends RestAdminBaseController
{
    /**
     * 资源列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/assets",
     *     summary="资源列表",
     *     description="资源列表",
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
        $result = AssetModel::alias('a')
            ->order('create_time', 'DESC')
            ->paginate(10);

        if(!$result->isEmpty()){
            $result->load(['user']);
            $result->visible(['user.user_type','user.sex','user.user_login','user.user_nickname','user.avatar']);
        }


        $this->success('success', ['list' => $result->items(), 'total' => $result->total()]);
    }

    /**
     * 删除资源
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/assets/{id}",
     *     summary="删除资源",
     *     description="删除资源",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="资源id",
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
    public function delete()
    {
        if ($this->request->isDelete()) {
            $id            = $this->request->param('id');
            $file_filePath = AssetModel::where('id', $id)->value('file_path');
            $file          = 'upload/' . $file_filePath;
            $res           = true;
            if (file_exists($file)) {
                $res = unlink($file);
            }
            if ($res) {
                AssetModel::where('id', $id)->delete();
                $this->success(lang('DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }
    }

}
