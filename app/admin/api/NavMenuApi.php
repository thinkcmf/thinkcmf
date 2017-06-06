<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\api;

use app\admin\model\NavMenuModel;

class NavMenuApi
{
    // 分类列表 用于模板设计
    public function index($param = [])
    {
        $navMenuModel = new NavMenuModel();

        $where = [];

        if (!empty($param['keyword'])) {
            $where['name'] = ['like', "%{$param['keyword']}%"];
        }
        if (!empty($param['id'])) {
            $where['nav_id'] = $param['id'];
        }

        return $navMenuModel->where($where)->select();
    }

}