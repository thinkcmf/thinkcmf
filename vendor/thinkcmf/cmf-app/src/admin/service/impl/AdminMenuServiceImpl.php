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
    private $model;

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

    /**
     * 后台菜单列表,用于后台首页左侧菜单
     * @return mixed
     */
    public function menus($adminId)
    {
        $menus = $this->model->where('type', 'in', [0, 1])->order('list_order asc')->select()->toArray();
        if ($adminId != 1) {
            foreach ($menus as $key => $menu) {
//                adminMenu.App + "/" + adminMenu.Controller + "/" + adminMenu.Action
                $ruleName = "{$menu['app']}/{$menu['controller']}/{$menu['action']}";
                if (!cmf_auth_check($adminId, $ruleName)) {
                    $menus[$key]['status'] = 0;
                }
            }
        }
        return $menus;
    }

}
