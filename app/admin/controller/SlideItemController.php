<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Db;
use cmf\controller\AdminBaseController;

class SlideItemController extends AdminBaseController
{
    /**
     * 幻灯片页面列表
     */
    public function index()
    {
        $slidePostModel = new SlideModel();
        $list           = $slidePostModel->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 幻灯片页面添加
     */
    public function add()
    {
        $categories = Db::name('slide')->where('status', 1)->select();
        $this->assign('categories', $categories);
        return $this->fetch();
    }

    /**
     * 幻灯片页面添加提交保存
     */
    public function addPost()
    {
        $data   = $this->request->param();
        Db::name('slideItem')->insert($data['post']);
        $this->success("添加成功！", url("slideItem/index"));
    }

    /**
     * 幻灯片页面编辑
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
     * 幻灯片页面编辑提交保存
     */
    public function editPost()
    {
        $data           = $this->request->param();
        $slidePostModel = new SlideModel();
        $result         = $slidePostModel->validate(true)->save($data, ['id' => $data['id']]);
        if ($result === false) {
            $this->error($slidePostModel->getError());
        }

        $this->success("修改成功！", url("slide/index"));
    }

    /**
     * 幻灯片页面删除
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        SlideModel::destroy($id);
        $this->success("删除成功！", url("slide/index"));
    }
}