<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// | Date: 2019/01/08
// | Time:上午 10:32
// +----------------------------------------------------------------------


namespace api\portal\service;


use api\portal\model\PortalCategoryModel;
use think\db\Query;

class PortalCategoryService
{
    /**
     * @param $filter
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function categories($filter)
    {
        $categoryModel = new PortalCategoryModel();
        //条件分解
        $field = empty($filter['field']) ? '*' : $filter['field'];
        $order = empty($filter['order']) ? ['-id'] : explode(',', $filter['order']);
        $page  = empty($filter['page']) ? '' : $filter['page'];
        $limit = empty($filter['limit']) ? '' : $filter['limit'];
        if (!empty($page)) {
            $categoryModel = $categoryModel->page($page);
        } elseif (!empty($limit)) {
            $categoryModel = $categoryModel->limit($limit);
        } else {
            $categoryModel = $categoryModel->limit(10);
        }
        //转化-+为desc、asc
        $orderArr = order_shift($order);

        $result = $categoryModel
            ->field($field)
            ->where('delete_time', 0)
            ->where('status', 1)
            ->where(function (Query $query) use ($filter) {
                if (!empty($filter['ids'])) {
                    $query->where('id', 'in', $filter['ids']);
                }
            })
            ->order($orderArr)
            ->select();
        return $result;
    }
}