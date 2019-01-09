<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\portal\controller;

use api\portal\service\PortalCategoryService;
use cmf\controller\RestBaseController;
use api\portal\model\PortalCategoryModel;

class CategoriesController extends RestBaseController
{
    /**
     * 获取分类列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $params          = $this->request->get();
        $categoryService = new PortalCategoryService();
        $data            = $categoryService->categories($params);
        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = $data;
        } else {
            $response = ['list' => $data];
        }

        $this->success('请求成功!', $response);
    }

    /**
     * 显示指定的分类
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        $categoryModel = new PortalCategoryModel();
        $data          = $categoryModel
            ->where('delete_time', 0)
            ->where('status', 1)
            ->where('id', $id)
            ->find();
        if ($data) {
            $this->success('请求成功！', $data);
        } else {
            $this->error('该分类不存在！');
        }

    }

    /**
     * 获取指定分类的子分类列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function subCategories()
    {
        $id = $this->request->get('category_id', 0, 'intval');

        $categoryModel = new PortalCategoryModel();
        $categories    = $categoryModel->where(['parent_id' => $id])->select();
        if (!$categories->isEmpty()) {
            $this->success('请求成功', ['categories' => $categories]);
        } else {
            $this->error('该分类下没有子分类！');
        }


    }
}