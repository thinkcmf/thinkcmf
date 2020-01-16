<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\RecycleBinModel;
use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class RecycleBinController extends AdminBaseController
{
    /**
     * 回收站
     * @adminMenu(
     *     'name'   => '回收站',
     *     'parent' => '',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '回收站',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('admin_recycle_bin_index_view');

        if (!empty($content)) {
            return $content;
        }

        // 获取分页显示
        $list = (new RecycleBinModel())->order('create_time desc')->paginate(20);

        $this->assign([
            'list' => $list,
            'page' => $list->render()
        ]);
        return $this->fetch();
    }

    /**
     * 回收站还原
     * @adminMenu(
     *     'name'   => '回收站还原',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '回收站还原',
     *     'param'  => ''
     * )
     */
    public function restore()
    {
        $this->operate($this->request->param('ids'), false);
        $this->success('还原成功');
    }

    /**
     * 回收站彻底删除
     * @adminMenu(
     *     'name'   => '回收站彻底删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '回收站彻底删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $this->operate($this->request->param('ids'));
        $this->success('删除成功');
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
    public function clear()
    {
        $this->operate(null);
        $this->success('回收站已清空');
    }

    /**
     * 统一处理删除、还原
     * @param bool  $isDelete 是否是删除操作
     * @param array $ids      处理的资源id集
     */
    private function operate($ids, $isDelete = true)
    {
        $records = RecycleBinModel::all($ids);

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