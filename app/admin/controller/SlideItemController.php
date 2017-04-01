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
    /**幻灯片列表**/
    public function index()
    {
        $slidePostModel = new SlideModel();
        $list           = $slidePostModel->select();
        $this->assign('list', $list);
        return $this->fetch();
    }
    /**幻灯片添加**/
    public function add()
    {
        $categories = Db::name('slide')->where('status',1)->select();
        $this->assign('categories',$categories);
        return $this->fetch();
    }

    /**幻灯片分类提交**/
    public function addPost()
    {
        $data           = $this->request->param();
        $result = Db::name('slideItem')->insert($data['post']);
        if ($result) {
            $this->success("添加成功！", url("slideItem/index"));
        }else{
            $this->error("添加失败！", url("slideItem/add"));
        }
    }

    /**分类编辑**/
    public function edit()
    {
        $id             = $this->request->param('id');
        $slidePostModel = new SlideModel();
        $result         = $slidePostModel->where('id', $id)->find();
        $this->assign('result', $result);
        return $this->fetch();
    }

    /**分类编辑提交**/
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

    /**删除幻灯片**/
    public function delete()
    {
        $id             = $this->request->param();
        $result = SlideModel::destroy($id);
        if ($result) {
            $this->success("删除成功！", url("slide/index"));
        }else{
            $this->error("删除失败！");
        }
    }
}