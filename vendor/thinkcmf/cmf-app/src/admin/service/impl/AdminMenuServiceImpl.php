<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 <449134904@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\service\impl;


use app\admin\model\AdminMenuModel;
use app\admin\service\AdminMenuService;

class AdminMenuServiceImpl implements AdminMenuService
{
    public function __construct(AdminMenuModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param string[] $order
     * @return mixed
     * @author 小夏
     * @email  449134904@qq.com
     * @date   2020-11-21 21:44:21
     */
    public function getAll($order = ["app" => "ASC", "controller" => "ASC", "action" => "ASC"])
    {
        return $this->model->order($order)->select();
    }
}
