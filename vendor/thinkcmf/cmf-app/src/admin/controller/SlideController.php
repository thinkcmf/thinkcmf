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
namespace app\admin\controller;

use app\admin\model\RecycleBinModel;
use app\admin\model\SlideItemModel;
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
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $content = hook_one('admin_slide_index_view');

        if (!empty($content)) {
            return $content;
        }

        $slidePostModel = new SlideModel();
        $slides         = $slidePostModel->where('delete_time', 0)->select();
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
        if ($this->request->isPost()) {
            $data           = $this->request->param();
            $slidePostModel = new SlideModel();
            $result         = $this->validate($data, 'Slide');
            if ($result !== true) {
                $this->error($result);
            }
            $slidePostModel->save($data);

            $this->success("添加成功！", url("Slide/index"));
        }
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
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'Slide');
            if ($result !== true) {
                $this->error($result);
            }
            $slidePostModel = SlideModel::find($data['id']);
            $slidePostModel->save($data);
            $this->success("保存成功！", url("Slide/index"));
        }
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
        if ($this->request->isPost()) {
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
            $this->success("删除成功！", url("Slide/index"));
        }
    }
}
