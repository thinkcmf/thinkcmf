<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// | Date: 2019/01/11
// | Time:下午 04:32
// +----------------------------------------------------------------------
namespace api\user\service;


use api\user\model\CommentModel;
use think\db\Query;

class CommentService
{
    /**
     * 获取用户评论列表
     * @param $filter
     * @return array|bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userComments($filter)
    {
        $page    = empty($filter['page']) ? '1' : $filter['page'];
        $comment = new CommentModel();
        $result  = $comment
            ->where('delete_time', 0)
            ->where('status', 1)
            ->where(function (Query $query) use ($filter) {
                if (!empty($filter['user_id'])) {
                    $query->where('user_id', $filter['user_id']);
                }
                if (!empty($filter['object_id'])) {
                    $query->where('object_id', $filter['object_id']);
                }
                if (!empty($filter['table_name'])) {
                    $query->where('table_name', $filter['table_name']);
                }
            })
            ->page($page)
            ->order('create_time desc')
            ->select();
        return $result;
    }
}
