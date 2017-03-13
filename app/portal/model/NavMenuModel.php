<?php
namespace app\portal\model;

use think\Model;
use tree\Tree;
use think\Db;

class NavMenuModel extends Model
{
    /**
     * 获取某导航下所有菜单树形结构数组
     * @param int $navId 导航 id
     * @return array
     */
    public function navMenusTreeArray($navId = 0)
    {
        if (empty($navId)) {
            $navId = Db::name('nav')->where('is_main', 1)->value('id');
        }
        $navMenus = $this->where('nav_id', $navId)->where('status', 1)->order('list_order ASC')->select()->toArray();

        $navMenusTree = [];
        if (!empty($navMenus)) {
            $tree = new Tree();

            $tree->init($navMenus);

            $navMenusTree = $tree->getTreeArray(0);
        }

        return $navMenusTree;
    }

    /**
     * 获取某导航菜单下的所有子菜单树形结构数组
     * @param $menuId 导航菜单 id
     * @return array
     */
    public function subNavMenusTreeArray($menuId)
    {

        $navId = $this->where('id', $menuId)->where('status', 1)->value('nav_id');

        if (empty($navId)) {
            return [];
        }

        $navMenus = $this->where('nav_id', $navId)->where('status', 1)->order('list_order ASC')->select()->toArray();

        $navMenusTree = [];
        if (!empty($navMenus)) {
            $tree = new Tree();

            $tree->init($navMenus);

            $navMenusTree = $tree->getTreeArray($menuId);
        }

        return $navMenusTree;
    }


}