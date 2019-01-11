<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// | Date: 2019/01/09
// | Time:下午 06:10
// +----------------------------------------------------------------------


namespace api\portal\service;


use api\portal\model\PortalPostModel;
use api\portal\model\PortalTagModel;
use think\db\Query;

class PortalTagService
{
    /**
     * 获取标签列表
     * @param array $filter 参数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function tagList($filter)
    {
        $field    = empty($filter['field']) ? '*' : $filter['field'];
        $page     = empty($filter['page']) ? '' : $filter['page'];
        $limit    = empty($filter['limit']) ? '' : $filter['limit'];
        $order    = empty($filter['order']) ? ['-id'] : explode(',', $filter['order']);
        $orderArr = order_shift($order);
        $tagModel = new PortalTagModel();
        if (!empty($page)) {
            $tagModel = $tagModel->page($page);
        } elseif (!empty($limit)) {
            $tagModel = $tagModel->limit($limit);
        } else {
            $tagModel = $tagModel->limit(10);
        }

        $result = $tagModel
            ->field($field)
            ->where('status', 1)
            ->where(function (Query $query) use ($filter) {
                if (!empty($filter['id'])) {
                    $query->where('id', $filter['id']);
                }
            })
            ->order($orderArr)
            ->select();
        return $result;
    }

    /**
     * @param $filter
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function portalTagArticles($filter)
    {
        $tagModel = new PortalTagModel();
        $tag      = $tagModel
            ->with([
                'articles' => function (Query $query) use ($filter) {
                    $field = empty($filter['field']) ? 'post.*' : explode(',', $filter['field']);
                    //转为查询条件
                    if (is_array($field)) {
                        foreach ($field as $key => $value) {
                            $field[$key] = 'post.' . $value;
                        }
                        $field = implode(',', $field);
                    }
                    $page     = empty($filter['page']) ? '' : $filter['page'];
                    $limit    = empty($filter['limit']) ? '10' : $filter['limit'];
                    $order    = empty($filter['order']) ? ['-post.id'] : explode(',', $filter['order']);
                    $orderArr = order_shift($order);
                    $query->field($field);
                    if (!empty($page)) {
                        $query->page($page);
                    } elseif (!empty($limit)) {
                        $query->limit($limit);
                    } else {
                        $query->limit(10);
                    }
                    $query->order($orderArr);
                    $query->hidden(['pivot']);
                }
            ])
            ->where('id', $filter['id'])
            ->find();
        return $tag;
    }
}