<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
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
    function index()
    {
        $content = hook_one('admin_recycle_bin_index_view');

        if (!empty($content)) {
            return $content;
        }

        $recycleBinModel = new RecycleBinModel();
        $list = $recycleBinModel->order('create_time desc')->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list', $list);
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
    function restore()
    {

        $id     = $this->request->param('id');
        $result = Db::name('recycleBin')->where(['id' => $id])->find();

        $tableName = explode('#', $result['table_name']);
        $tableName = $tableName[0];
        //还原资源
        if ($result) {
            $res = Db::name($tableName)
                ->where(['id' => $result['object_id']])
                ->update(['delete_time' => '0']);
            if ($tableName =='portal_post'){
                Db::name('portal_category_post')->where('post_id',$result['object_id'])->update(['status'=>1]);
                Db::name('portal_tag_post')->where('post_id',$result['object_id'])->update(['status'=>1]);
            }

            if ($res) {
                $re = Db::name('recycleBin')->where('id', $id)->delete();
                if ($re) {
                    $this->success("还原成功！");
                }
            }
        }
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
    function delete()
    {
        $id     = $this->request->param('id');
        $result = Db::name('recycleBin')->where(['id' => $id])->find();
        //删除资源
        if ($result) {

            //页面没有单独的表.
            if($result['table_name'] === 'portal_post#page'){
                $re = Db::name('portal_post')->where('id', $result['object_id'])->delete();
                //消除路由
                $routeModel = new RouteModel();
                $routeModel->setRoute('', 'portal/Page/index', ['id' => $result['object_id']], 2, 5000);
                $routeModel->getRoutes(true);
            }else{
                $re = Db::name($result['table_name'])->where('id', $result['object_id'])->delete();
            }

            if ($re) {
                $res = Db::name('recycleBin')->where('id', $id)->delete();
                if($result['table_name'] === 'portal_post'){
                    Db::name('portal_category_post')->where('post_id',$result['object_id'])->delete();
                    Db::name('portal_tag_post')->where('post_id',$result['object_id'])->delete();
                }
                if ($res) {
                    $this->success("删除成功！");
                }

            }
        }
    }
}