<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------
namespace api\portal\service;

use api\portal\model\PortalPostModel;
use think\db\Query;

class PortalPostService
{
    //模型关联方法
    protected $relationFilter = ['user'];

    /**
     * 文章列表
     * @param      $filter
     * @param bool $isPage
     * @return array|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postArticles($filter, $isPage = false)
    {
        $join = [];

        $field = empty($filter['field']) ? 'a.*' : explode(',', $filter['field']);
        //转为查询条件
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $field[$key] = 'a.' . $value;
            }
            $field = implode(',', $field);
        }
        $page     = empty($filter['page']) ? '' : $filter['page'];
        $limit    = empty($filter['limit']) ? '' : $filter['limit'];
        $order    = empty($filter['order']) ? ['-update_time'] : explode(',', $filter['order']);
        $category = empty($filter['category_id']) ? 0 : intval($filter['category_id']);
        if (!empty($category)) {
            array_push($join, ['__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id']);
            $field = $field.',b.id AS post_category_id,b.list_order,b.category_id';
        }

        $orderArr = order_shift($order);

        $portalPostModel = new PortalPostModel();


        if (!empty($page)) {
            $portalPostModel = $portalPostModel->page($page);
        } elseif (!empty($limit)) {
            $portalPostModel = $portalPostModel->limit($limit);
        } else {
            $portalPostModel = $portalPostModel->limit(10);
        }

        $articles = $portalPostModel
            ->alias('a')
            ->field($field)
            ->join($join)
            ->where('a.create_time', '>=', 0)
            ->where('a.delete_time', 0)
            ->where('a.post_status', 1)
            ->where(function (Query $query) use ($filter, $isPage) {
                if (!empty($filter['user_id'])) {
                    $query->where('a.user_id', $filter['user_id']);
                }
                $category = empty($filter['category_id']) ? 0 : intval($filter['category_id']);
                if (!empty($category)) {
                    $query->where('b.category_id', $category);
                }
                $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
                $endTime   = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
                if (!empty($startTime)) {
                    $query->where('a.published_time', '>=', $startTime);
                }
                if (!empty($endTime)) {
                    $query->where('a.published_time', '<=', $endTime);
                }
                $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
                if (!empty($keyword)) {
                    $query->where('a.post_title', 'like', "%$keyword%");
                }
                if ($isPage) {
                    $query->where('a.post_type', 2);
                } else {
                    $query->where('a.post_type', 1);
                }
                if (!empty($filter['recommended'])) {
                    $query->where('a.recommended', 1);
                }
                if (!empty($filter['ids'])) {
                    $ids = str_to_arr($filter['ids']);
                    $query->where('a.id', 'in', $ids);
                }
            })
            ->order($orderArr)
            ->select();

        return $articles;
    }

}
