<?php
namespace app\portal\api;

use app\portal\model\PortalCategoryModel;

class CategoryApi
{
    // 分类列表 用于模板设计
    public function index($param = [])
    {
        $portalCategoryModel = new PortalCategoryModel();

        $where = [];

        if (!empty($param['keyword'])) {
            $where['name'] = ['like', "%{$param['keyword']}%"];
        }

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        return $portalCategoryModel->where($where)->select();
    }

}