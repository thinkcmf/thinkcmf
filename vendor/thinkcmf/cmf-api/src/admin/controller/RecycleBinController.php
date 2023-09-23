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

use app\admin\model\RecycleBinModel;
use app\admin\model\RouteModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;
use think\facade\Db;
use think\Exception;
use think\exception\PDOException;

class RecycleBinController extends RestAdminBaseController
{
    /**
     * 回收站文件列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/recycle/bin/items",
     *     summary="回收站文件列表",
     *     description="回收站文件列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {
     *                      "id": 2,"object_id": 5,"create_time": 1693620556,"table_name": "portal_post","name": "asfsdf","user_id": 1
     *                  }
     *              },
     *              "total":10
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
        $recycleBinModel = new RecycleBinModel();
        $list            = $recycleBinModel->order('create_time desc')->paginate(10);

        if(!$list->isEmpty()){
            $list->load(['user']);
            $list->visible(['user.user_type','user.sex','user.user_login','user.user_nickname','user.avatar']);
        }
        $this->success('success', ['list' => $list->items(), 'total' => $list->total()]);
    }

    /**
     * 回收站文件还原
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/recycle/bin/restore",
     *     summary="回收站文件还原",
     *     description="回收站文件还原",
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
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function restore()
    {
        if ($this->request->isPost()) {
            $ids = $this->request->param('ids');
            $this->operate($ids, false);
            $this->success('还原成功');
        }
    }

    /**
     * 彻底删除回收站文件
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/recycle/bin/items",
     *     summary="彻底删除回收站文件",
     *     description="彻底删除回收站文件",
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
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function delete()
    {
        if ($this->request->isDelete()) {
            $ids = $this->request->param('ids');
            $this->operate($ids);
            $this->success(lang('DELETE_SUCCESS'));
        }
    }

    /**
     * 清空回收站
     * @adminMenu(
     *     'name'   => '清空回收站',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '一键清空回收站',
     *     'param'  => ''
     * )
     */
    /**
     * 清空回收站
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/recycle/bin/clear",
     *     summary="清空回收站",
     *     description="清空回收站",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "回收站已清空","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function clear()
    {
        if ($this->request->isDelete()) {
            $this->operate(null);
            $this->success('回收站已清空');
        }
    }

    /**
     * 统一处理删除、还原
     * @param bool  $isDelete 是否是删除操作
     * @param array $ids      处理的资源id集
     */
    private function operate($ids, $isDelete = true)
    {
        if (!empty($ids) && !is_array($ids)) {
            $ids = [$ids];
        }
        if (is_null($ids)) {
            $records = RecycleBinModel::select();
        } else {
            $records = RecycleBinModel::select($ids);
        }


        if ($records) {
            try {
                Db::startTrans();
                $desIds = [];
                foreach ($records as $record) {
                    $desIds[] = $record['id'];
                    if ($isDelete) {
                        // 删除资源
                        if ($record['table_name'] === 'portal_post#page') {
                            // 页面没有单独的表，需要单独处理
                            Db::name('portal_post')->delete($record['object_id']);

                            // 消除路由
                            $routeModel = new RouteModel();
                            $routeModel->setRoute('', 'portal/Page/index', ['id' => $record['object_id']], 2, 5000);
                            $routeModel->getRoutes(true);
                        } else {
                            Db::name($record['table_name'])->delete($record['object_id']);
                        }

                        // 如果是文章表，删除相关数据
                        if ($record['table_name'] === 'portal_post') {
                            Db::name('portal_category_post')->where('post_id', '=', $record['object_id'])->delete();
                            Db::name('portal_tag_post')->where('post_id', '=', $record['object_id'])->delete();
                        }
                    } else {
                        // 还原资源
                        $tableNameArr = explode('#', $record['table_name']);
                        $tableName    = $tableNameArr[0];

                        $result = Db::name($tableName)->where('id', '=', $record['object_id'])->update(['delete_time' => '0']);
                        if ($result) {
                            if ($tableName === 'portal_post') {
                                Db::name('portal_category_post')->where('post_id', '=', $record['object_id'])->update(['status' => 1]);
                                Db::name('portal_tag_post')->where('post_id', '=', $record['object_id'])->update(['status' => 1]);
                            }
                        }
                    }
                }
                // 删除回收站数据
                RecycleBinModel::destroy($desIds);
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error('数据库错误', $e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($isDelete ? '删除' : '还原' . '失败', $e->getMessage());
            }
        }
    }
}
