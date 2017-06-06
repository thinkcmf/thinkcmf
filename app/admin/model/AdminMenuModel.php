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
namespace app\admin\model;

use think\Model;
use think\Cache;

class AdminMenuModel extends Model
{
    //验证菜单是否超出三级
    public function checkParentId($parentId)
    {
        $find = $this->where(["id" => $parentId])->getField("parent_id");
        if ($find) {
            $find2 = $this->where(["id" => $find])->getField("parent_id");
            if ($find2) {
                $find3 = $this->where(["id" => $find2])->getField("parent_id");
                if ($find3) {
                    return false;
                }
            }
        }
        return true;
    }

    //验证action是否重复添加
    public function checkAction($data)
    {
        //检查是否重复添加
        $find = $this->where($data)->find();
        if ($find) {
            return false;
        }
        return true;
    }

    //验证action是否重复添加
    public function checkActionUpdate($data)
    {
        //检查是否重复添加
        $id = $data['id'];
        unset($data['id']);
        $find = $this->field('id')->where($data)->find();
        if (isset($find['id']) && $find['id'] != $id) {
            return false;
        }
        return true;
    }


    /**
     * 按父ID查找菜单子项
     * @param int $parentId 父菜单ID
     * @param boolean $withSelf 是否包括他自己
     * @return mixed
     */
    public function adminMenu($parentId, $withSelf = false)
    {
        //父节点ID
        $parentId = (int)$parentId;
        $result   = $this->where(['parent_id' => $parentId, 'status' => 1])->order("list_order", "ASC")->select();

        if ($withSelf) {
            $result2[] = $this->where(['id' => $parentId])->find();
            $result    = array_merge($result2, $result);
        }

        //权限检查
        if (cmf_get_current_admin_id() == 1) {
            //如果是超级管理员 直接通过
            return $result;
        }

        $array = [];

        foreach ($result as $v) {

            //方法
            $action = $v['action'];

            //public开头的通过
            if (preg_match('/^public_/', $action)) {
                $array[] = $v;
            } else {

                if (preg_match('/^ajax_([a-z]+)_/', $action, $_match)) {

                    $action = $_match[1];
                }

                $rule_name = strtolower($v['app'] . "/" . $v['controller'] . "/" . $action);
//                print_r($rule_name);
                if (cmf_auth_check(cmf_get_current_admin_id(), $rule_name)) {
                    $array[] = $v;
                }

            }
        }

        return $array;
    }

    /**
     * 获取菜单 头部菜单导航
     * @param string $parentId 菜单id
     * @return mixed|string
     */
    public function subMenu($parentId = '', $bigMenu = false)
    {
        $array   = $this->adminMenu($parentId, 1);
        $numbers = count($array);
        if ($numbers == 1 && !$bigMenu) {
            return '';
        }
        return $array;
    }

    /**
     * 菜单树状结构集合
     */
    public function menuTree()
    {
        $data = $this->getTree(0);
        return $data;
    }

    /**
     * 取得树形结构的菜单
     * @param $myId
     * @param string $parent
     * @param int $Level
     * @return bool|null
     */
    public function getTree($myId, $parent = "", $Level = 1)
    {
        $data = $this->adminMenu($myId);
        $Level++;
        if (count($data) > 0) {
            $ret = NULL;
            foreach ($data as $a) {
                $id         = $a['id'];
                $name       = $a['app'];
                $controller = ucwords($a['controller']);
                $action     = $a['action'];
                //附带参数
                $params = "";
                if ($a['param']) {
                    $params = "?" . htmlspecialchars_decode($a['param']);
                }
                $array = [
                    "icon"   => $a['icon'],
                    "id"     => $id . $name,
                    "name"   => $a['name'],
                    "parent" => $parent,
                    "url"    => url("{$name}/{$controller}/{$action}{$params}"),
                    'lang'   => strtoupper($name . '_' . $controller . '_' . $action)
                ];


                $ret[$id . $name] = $array;
                $child            = $this->getTree($a['id'], $id, $Level);
                //由于后台管理界面只支持三层，超出的不层级的不显示
                if ($child && $Level <= 3) {
                    $ret[$id . $name]['items'] = $child;
                }

            }
            return $ret;
        }

        return false;
    }

    /**
     * 更新缓存
     * @param  $data
     * @return array
     */
    public function menuCache($data = null)
    {
        if (empty($data)) {
            $data = $this->order("list_order", "ASC")->column('');
            Cache::set('Menu', $data, 0);
        } else {
            Cache::set('Menu', $data, 0);
        }
        return $data;
    }

    /**
     * 后台有更新/编辑则删除缓存
     * @param type $data
     */
    public function _before_write(&$data)
    {
        parent::_before_write($data);
        F("Menu", NULL);
    }

    //删除操作时删除缓存
    public function _after_delete($data, $options)
    {
        parent::_after_delete($data, $options);
        $this->_before_write($data);
    }

    public function menu($parentId, $with_self = false)
    {
        //父节点ID
        $parentId = (int)$parentId;
        $result   = $this->where(['parent_id' => $parentId])->select();
        if ($with_self) {
            $result2[] = $this->where(['id' => $parentId])->find();
            $result    = array_merge($result2, $result);
        }
        return $result;
    }

    /**
     * 得到某父级菜单所有子菜单，包括自己
     * @param number $parentId
     */
    public function get_menu_tree($parentId = 0)
    {
        $menus = $this->where(["parent_id" => $parentId])->order(["list_order" => "ASC"])->select();

        if ($menus) {
            foreach ($menus as $key => $menu) {
                $children = $this->get_menu_tree($menu['id']);
                if (!empty($children)) {
                    $menus[$key]['children'] = $children;
                }
                unset($menus[$key]['id']);
                unset($menus[$key]['parent_id']);
            }
            return $menus;
        } else {
            return $menus;
        }

    }

}
