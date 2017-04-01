<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\SlideModel;
use cmf\controller\AdminBaseController;

class SlideController extends AdminBaseController
{
    //幻灯片列表
    public function index()
    {
        $slidePostModel = new SlideModel();
        $list           = $slidePostModel->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    //  幻灯片
    public function add()
    {
        return $this->fetch();
    }

    //幻灯片提交
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

    //编辑
    public function edit()
    {
        $id             = $this->request->param('id');
        $slidePostModel = new SlideModel();
        $result         = $slidePostModel->where('id', $id)->find();
        $this->assign('result', $result);
        return $this->fetch();
    }

    //编辑提交
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

    //删除幻灯片
    public function delete()
    {
        $id     = $this->request->param();
        $result = SlideModel::destroy($id);
        if ($result) {
            $this->success("删除成功！", url("slide/index"));
        } else {
            $this->error("删除失败！");
        }
    }
}