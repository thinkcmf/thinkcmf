<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\PortalCategoryModel;
use think\Db;

class AdminCategoryController extends AdminBaseController
{
    // 文章分类列表
    public function index()
    {
        $portalCategoryModel = new PortalCategoryModel();
        $categoryTree        = $portalCategoryModel->adminCategoryTree();

        $categories = $portalCategoryModel->where(['delete_time'=>0])->select();

        $this->assign('categories', $categories);
        $this->assign('category_tree', $categoryTree);
        return $this->fetch();
    }

    // 添加分类
    public function add()
    {
        $portalCategoryModel = new PortalCategoryModel();
        $categoriesTree      = $portalCategoryModel->adminCategoryTree();

        $this->assign('categories_tree', $categoriesTree);
        return $this->fetch();
    }

    // 添加分类提交保存
    public function addPost()
    {
        $portalCategoryModel = new PortalCategoryModel();

        $data = $this->request->param();

        $result = $this->validate($data, 'PortalCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $portalCategoryModel->insert($data);

        $this->success('添加成功!',url('AdminCategory/index'));

    }

    // 编辑分类
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $category = PortalCategoryModel::get($id)->toArray();
            $this->assign($category);

            $portalCategoryModel = new PortalCategoryModel();
            $categoriesTree      = $portalCategoryModel->adminCategoryTree($id);

            $this->assign('categories_tree', $categoriesTree);
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }

    }

    // 编辑分类提交保存
    public function editPost()
    {
        $data = $this->request->param();

        $result = $this->validate($data, 'PortalCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $portalCategoryModel = new PortalCategoryModel();
        $portalCategoryModel->isUpdate(true)->save($data);

        $this->success('保存成功!');
    }

    // 文章分类选择对话框
    public function select()
    {
        $ids                 = $this->request->param('ids');
        $portalCategoryModel = new PortalCategoryModel();
        $categoryTree        = $portalCategoryModel->adminCategoryTree();

        $categories = $portalCategoryModel->where([])->select();

        $this->assign('categories', $categories);
        $this->assign('selectedIds', explode(',', $ids));
        $this->assign('category_tree', $categoryTree);
        return $this->fetch();
    }
    //文章分类删除
    public function delete()
    {
        $portalCategoryModel = new PortalCategoryModel();
        $id = $this->request->param('id');
        //获取删除的内容
        $res = $portalCategoryModel->where('id',$id)->find();
        $data = [
            'object_id'=>$res['id'],
            'create_time'=> time(),
            'table_name' => 'portal_category',
            'name'=>$res['name']
        ];
        $result = $portalCategoryModel
            ->where('id',$id)
            ->update(['delete_time' => time()]);
        if ($result){
            Db::name('recycleBin')->insert($data);
            $this->success('删除成功!');
        }else{
                $this->error('删除失败');
        }
    }
}
