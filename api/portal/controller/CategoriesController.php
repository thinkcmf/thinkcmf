<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\portal\controller;

use cmf\controller\RestBaseController;
use api\portal\model\PortalCategoryModel;

class CategoriesController extends RestBaseController
{
    protected $categoryModel;

    public function __construct(PortalCategoryModel $categoryModel)
    {
        parent::__construct();
        $this->categoryModel = $categoryModel;
    }

    /**
     * 获取分类列表
     */
    public function index()
    {
        $params = $this->request->get();
        $data   = $this->categoryModel->getDatas($params);

        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = $data;
        } else {
            $response = ['list' => $data];
        }

        $this->success('请求成功!', $response);
    }

    /**
     * 显示指定的分类
     * @param int $id
     */
    public function read($id)
    {
        $params       = $this->request->get();
        $params['id'] = $id;
        $data         = $this->categoryModel->getDatas($params);
        $this->success('请求成功!', $data);
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

        $categories = $this->categoryModel->where(['parent_id' => $id])->select();

        $this->success('请求成功', ['categories' => $categories]);

    }
}