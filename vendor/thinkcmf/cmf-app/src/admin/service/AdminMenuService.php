<?php


namespace app\admin\service;


interface AdminMenuService
{
    /**
     * @param $order
     * @return mixed
     * @author 小夏
     * @email  449134904@qq.com
     * @date   2020-11-21 21:44:21
     */
    public function getAll($order);

    /**
     * 后台菜单列表,用于后台首页左侧菜单
     * @param $userId
     * @return mixed
     */
    public function menus($userId);
}
