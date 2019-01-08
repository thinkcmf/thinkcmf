<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wuwu <15093565100@163.com>
// +----------------------------------------------------------------------
namespace api\portal\controller;

use api\portal\model\PortalCategoryModel;
use api\portal\service\PortalPostService;
use cmf\controller\RestBaseController;

class ListsController extends RestBaseController
{

    /**
     * 推荐文章列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recommended()
    {
        $param                = $this->request->param();
        $param['recommended'] = true;

        $portalPostService = new PortalPostService();
        $articles          = $portalPostService->postArticles($param);
        //是否需要关联模型
        if (!$articles->isEmpty()) {
            if (!empty($param['relation'])) {
                $allowedRelations = allowed_relations(['user', 'categories'], $param['relation']);
                if (!empty($allowedRelations)) {
                    $articles->load($allowedRelations);
                    $articles->append($allowedRelations);
                }
            }
        }
        $this->success('ok', ['list' => $articles]);
    }

    /**
     * 分类文章列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategoryPostLists()
    {
        $categoryId = $this->request->param('category_id', 0, 'intval');

        $portalCategoryModel = new  PortalCategoryModel();
        $findCategory        = $portalCategoryModel->where('id', $categoryId)->find();

        //分类是否存在
        if (empty($findCategory)) {
            $this->error('分类不存在！');
        }

        $param = $this->request->param();

        $portalPostService = new PortalPostService();
        $articles          = $portalPostService->postArticles($param);
        //是否需要关联模型
        if (!$articles->isEmpty()) {
            if (!empty($param['relation'])) {
                $allowedRelations = allowed_relations(['user', 'categories'], $param['relation']);
                if (!empty($allowedRelations)) {
                    $articles->load($allowedRelations);
                    $articles->append($allowedRelations);
                }
            }
        }
        $this->success('ok', ['list' => $articles]);
    }

}
