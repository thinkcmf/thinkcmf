<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\service;


use app\admin\model\AdminMenuModel;

class DevService
{
    /**
     * 获取所有友情链接
     */
    public static function devMenus()
    {
        $devMenuId = AdminMenuModel::where(['app' => 'admin', 'controller' => 'Dev', 'action' => 'index'])->value('id');
        return AdminMenuModel::where('parent_id', $devMenuId)->where('type', 1)->order('list_order asc')->select();
    }

}
