<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class RecycleController extends AdminBaseController
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
        $list = Db::name('recycleBin')->order('create_time desc')->paginate(10);
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
        $result = Db::name('recycleBin')->where(array('id' => $id))->find();
        //还原文章
        if ($result) {
            $res = Db::name($result['table_name'])
                ->where(['id'=>$result['object_id']])
                ->update(['delete_time' => '0']);
            if ($res){
                $re = Db::name('recycleBin')->where('id', $id)->delete();
                if ($re){
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
        $result = Db::name('recycleBin')->where(array('id' => $id))->find();
        //删除文章
        if ($result) {
            $re = Db::name($result['table_name'])->where('id', $result['object_id'])->delete();
            if ($re) {
                $res = Db::name('recycleBin')->where('id', $id)->delete();
                if ($res) {
                    $this->success("删除成功！");
                }
            }
        }
    }
}