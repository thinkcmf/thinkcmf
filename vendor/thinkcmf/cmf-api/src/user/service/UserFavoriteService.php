<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// | Date: 2019/01/11
// | Time:下午 03:02
// +----------------------------------------------------------------------


namespace api\user\service;


use api\user\model\UserFavoriteModel;

class UserFavoriteService
{
    /**
     * 我的收藏列表
     * @param $filter
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function favorites($filter)
    {
        $favoriteModel = new UserFavoriteModel();
        $page          = empty($filter['page']) ? '1' : $filter['page'];
        $result        = $favoriteModel
            ->where('user_id', $filter['user_id'])
            ->page($page)
            ->order('create_time desc')
            ->select();
        return $result;
    }
}