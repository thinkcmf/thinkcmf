<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\SlideModel;
use cmf\controller\AdminBaseController;

class SlideController extends AdminBaseController
{

    /**
     * 幻灯片列表
     * @adminMenu(
     *     'name'   => '幻灯片管理',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 40,
     *     'icon'   => '',
     *     'remark' => '幻灯片管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $slidePostModel = new SlideModel();
        $slides         = $slidePostModel->where(['delete_time' => ['eq', 0]])->select();
        $this->assign('slides', $slides);
        return $this->fetch();
    }

    /**
     * 添加幻灯片
     * @adminMenu(
     *     'name'   => '添加幻灯片',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加幻灯片',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加幻灯片提交
     * @adminMenu(
     *     'name'   => '添加幻灯片提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加幻灯片提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data           = $this->request->param();
        $slidePostModel = new SlideModel();
        $result         = $slidePostModel->validate(true)->save($data);
        if ($result === false) {
            $this->error($slidePostModel->getError());
        }
        $this->success("添加成功！", url("slide/index"));
    }

    /**
     * 编辑幻灯片
     * @adminMenu(
     *     'name'   => '编辑幻灯片',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑幻灯片',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id             = $this->request->param('id');
        $slidePostModel = new SlideModel();
        $result         = $slidePostModel->where('id', $id)->find();
        $this->assign('result', $result);
        return $this->fetch();
    }

    /**
     * 编辑幻灯片提交
     * @adminMenu(
     *     'name'   => '编辑幻灯片提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑幻灯片提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data           = $this->request->param();
        $slidePostModel = new SlideModel();
        $result         = $slidePostModel->validate(true)->save($data, ['id' => $data['id']]);
        if ($result === false) {
            $this->error($slidePostModel->getError());
        }
        $this->success("保存成功！", url("slide/index"));
    }

    /**
     * 删除幻灯片
     * @adminMenu(
     *     'name'   => '删除幻灯片',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除幻灯片',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id             = $this->request->param('id', 0, 'intval');
        $slidePostModel = new SlideModel();
        $slidePostModel->save(['delete_time' => time()], ['id' => $id]);
        //TODO 放进回收站
        $this->success("删除成功！", url("slide/index"));
    }
}