<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\admin\api;

use app\admin\model\NavMenuModel;

class NavMenuApi
{
    /**
     * 导航菜单模板数据源 用于模板设计
     *
     * @param array $param
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        $navMenuModel = new NavMenuModel();

        $result = $navMenuModel
            ->where(function (Query $query) use ($param) {
                if (!empty($param['keyword'])) {
                    $query->where('name', 'like', "%{$param['keyword']}%");
                }

                if (!empty($param['id'])) {
                    $query->where('nav_id', intval($param['id']));
                }
            })->select();

        return $result;
    }
}
