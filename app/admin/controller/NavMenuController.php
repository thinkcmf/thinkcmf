<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: kane <chengjin005@163.com> 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\NavModel;
use app\portal\model\NavMenuModel;
use cmf\controller\AdminBaseController;
use tree\Tree;


/**
 * Class NavMenuController 前台菜单管理控制器
 * @package app\admin\controller
 */
class NavMenuController extends AdminBaseController
{


    /**
     *  显示前台菜单
     */
    public function index()
    {

        $intNavId = $this->request->param("nav_id");
        //$intId       = $this->request->param("id");
        //$intParentId = $this->request->param("parentid");
        $navMenuModel = new NavMenuModel();

        if (empty($intNavId)) {
            $intNavId = $navMenuModel->value("nav_id");
        }

        $objResult = $navMenuModel->where("nav_id", $intNavId)->order(["list_order" => "ASC"])->select();
        $arrResult = $objResult ? $objResult->toArray() : [];

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $array = [];
        foreach ($arrResult as $r) {
            $r['str_manage'] = '<a href="' . url("NavMenu/add", ["parent_id" => $r['id'], "nav_id" => $r['nav_id']]) . '">添加子菜单</a> | <a href="'
                . url("NavMenu/edit", ["id" => $r['id'], "parent_id" => $r['parent_id'], "nav_id" => $r['nav_id']]) . '">修改</a> | <a class="js-ajax-delete" href="' . url("NavMenu/delete", ["id" => $r['id']]) . '">删除</a> ';
            $r['status']     = $r['status'] ? "显示" : "隐藏";
            $array[]         = $r;
        }

        $tree->init($array);
        $str       = "<tr>
            <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input input-order'></td>
            <td>\$id</td>
            <td >\$spacer\$label</td>
            <td>\$status</td>
            <td>\$str_manage</td>
        </tr>";
        $categorys = $tree->getTree(0, $str);
        $this->assign("categorys", $categorys);

        $objResult = $navMenuModel->select();
        $this->assign("navcats", $objResult ? $objResult->toArray() : []);
        $this->assign("navcid", $intNavId);

        return $this->fetch();
    }

    /**
     *  添加前台菜单
     */
    public function add()
    {
        $navModel     = new NavModel();
        $navMenuModel = new NavMenuModel();
        $intNavId     = $this->request->param("nav_id");
        $intParentId  = $this->request->param("parent_id");
        $objResult    = $navMenuModel->where("nav_id", $intNavId)->order(["list_order" => "ASC"])->select();
        $arrResult    = $objResult ? $objResult->toArray() : [];

        $tree       = new Tree();
        $tree->icon = ['&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ '];
        $tree->nbsp = '&nbsp;';
        $array      = [];

        foreach ($arrResult as $r) {
            $r['str_manage'] = '<a href="' . url("NavMenu/add", ["parent_id" => $r['id']]) . '">添加子菜单</a> | <a href="'
                . url("NavMenu/edit", ["id" => $r['id']]) . '">修改</a> | <a class="J_ajax_del" href="'
                . url("NavMenu/delete", ["id" => $r['id']]) . '">删除</a> ';
            $r['status']     = $r['status'] ? "显示" : "隐藏";
            $r['selected']   = $r['id'] == $intParentId ? "selected" : "";
            $array[]         = $r;
        }

        $tree->init($array);
        $str      = "<tr>
            <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input'></td>
            <td>\$id</td>
            <td >\$spacer\$label</td>
            <td>\$status</td>
            <td>\$str_manage</td>
        </tr>";
        $str      = "<option value='\$id' \$selected>\$spacer\$label</option>";
        $navTrees = $tree->getTree(0, $str);
        $this->assign("nav_trees", $navTrees);

        $objCats = $navModel->select();
        $this->assign("navcats", $objCats ? $objCats->toArray() : []);

        // $objResult = $navMenuModel->select();
        //$this->assign("navcats",$objResult?$objResult->toArray():array());
        $this->assign('navs', $navMenuModel->selectUrl());
        $this->assign("navcid", $intNavId);
        return $this->fetch();
    }

    /**
     * 执行新增请求
     */
    public function addPost()
    {


        $navMenuModel = new NavMenuModel();
        $arrData      = $this->request->post();


        if ($arrData['isSysUrl']) {
            if (strpos($arrData['href_select'], "http")) {
                $this->error("地址不能包含http字符");
            }
            $arrData['href'] = $arrData['href_select'];
        } else {
            if (strpos($arrData['href_input'], "http") === false) {
                $this->error("地址没有包含http字符!");
            }
            $arrData['href'] = $arrData['href_input'];
        }

        $navMenuModel->allowField(true)->isUpdate(false)->save($arrData);
        $intResultId = $navMenuModel->getLastInsID();

        $intParentId = $arrData['parent_id'] == 0 ? "0" : $arrData['parent_id'];
        $data        = [];
        if (empty($intParentId)) {
            $data['path'] = "0-$intResultId";
        } else {
            $objParent    = $navMenuModel->where("id", $intParentId)->find();
            $arrParent    = $objParent ? $objParent->toArray() : [];
            $data['path'] = $arrParent["path"] . "-$intResultId";
        }

        $navMenuModel->where(["id" => $intResultId])->update($data);
        $this->success(lang("EDIT_SUCCESS"), url("NavMenu/index"));

    }

    /**
     * 编辑前台菜单
     * @todo $str 如果用不到就删除掉吧
     * @return mixed
     */

    public function edit()
    {
        $navMenuModel = new NavMenuModel();
        $navModel     = new NavModel();
        $intNavId     = $this->request->param("nav_id");
        $intId        = $this->request->param("id");
        $intParentId  = $this->request->param("parent_id");
        $objResult    = $navMenuModel->where(["nav_id" => $intNavId, "id" => ["neq", $intId]])->order(["list_order" => "ASC"])->select();
        $arrResult    = $objResult ? $objResult->toArray() : [];

        $tree       = new Tree();
        $tree->icon = ['&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $array      = [];
        foreach ($arrResult as $r) {
            $r['str_manage'] = '<a href="' . url("NavMenu/add", ["parent_id" => $r['id'], "nav_id" => $intNavId]) . '">添加子菜单</a> | <a href="'
                . url("NavMenu/edit", ["id" => $r['id'], "nav_id" => $intNavId]) . '">修改</a> | <a class="J_ajax_del" href="'
                . url("NavMenu/delete", ["id" => $r['id'], "nav_id" => $intNavId]) . '">删除</a> ';
            $r['status']     = $r['status'] ? "显示" : "隐藏";
            $r['selected']   = $r['id'] == $intParentId ? "selected" : "";
            $array[]         = $r;
        }

        $tree->init($array);

        $str       = "<tr>
            <td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input'></td>
            <td>\$id</td>
            <td >\$spacer\$label</td>
            <td>\$status</td>
            <td>\$str_manage</td>
        </tr>";
        $str       = "<option value='\$id' \$selected>\$spacer\$label</option>";
        $nav_trees = $tree->getTree(0, $str);
        $this->assign("nav_trees", $nav_trees);


        $objCats = $navModel->select();

        $this->assign("navcats", $objCats ? $objCats->toArray() : []);

        $objNav = $navMenuModel->where("id", $intId)->find();
        $arrNav = $objNav ? $objNav->toArray() : [];

        $arrNav['hrefold'] = stripslashes($arrNav['href']);

        if (cmf_is_serialized($arrNav['hrefold'])) {
            $href = unserialize($arrNav['hrefold']);
        }

        if (empty($href)) {
            if ($arrNav['hrefold'] == "home") {
                $href = $this->request->root() . "/";
            } else {
                $href = $arrNav['hrefold'];
            }
        } else {
            $default_app = strtolower(config("DEFAULT_GROUP"));
            $href        = url($href['action'], $href['param']);
            $g           = config("VAR_GROUP");
            $href        = preg_replace("/\/$default_app\//", "/", $href);
            $href        = preg_replace("/$g=$default_app&/", "", $href);
        }


        $arrNav['href']     = $href;
        $arrNav['isSysUrl'] = strpos($arrNav['href'], "http") === false ? 1 : 0;

        $this->assign($arrNav);
        $this->assign('navs', $navMenuModel->selectUrl());
        $this->assign("navcid", $intNavId);
        $this->assign("intParentId", $intParentId);

        return $this->fetch();
    }


    /**
     *  编辑前台菜单提交保存
     */
    public function editPost()
    {
        $navMenuModel = new NavMenuModel();
        $intId        = $this->request->post('id');
        $arrData      = $this->request->post();


        $intParentId = empty($this->request->post('parent_id')) ? "0" : $this->request->post('parent_id');
        if (empty($parentid)) {
            $this->request->post('path', "0-" . $intId);
        } else {
            $objParent = $navMenuModel->where("id", $intParentId)->find();
            $arrParent = $objParent ? $objParent->toArray() : [];
            $this->request->post('path', $arrParent['path'] . "-" . $intId);

        }

        if ($arrData['isSysUrl']) {
            if (strpos($arrData['href_select'], "http")) {
                $this->error("地址不能包含http字符");
            }
            $arrData['href'] = $arrData['href_select'];
        } else {
            if (strpos($arrData['href_input'], "http") === false) {
                $this->error("地址没有包含http字符!");
            }
            $arrData['href'] = $arrData['href_input'];
        }
        $arrData['href'] = htmlspecialchars_decode($arrData['href']);

        $navMenuModel->update($arrData, ["id" => $arrData["id"]], true);
        $this->success(lang("EDIT_SUCCESS"), url("NavMenu/index"));

    }

    /**
     * 删除前台菜单
     */
    public function delete()
    {
        $navMenuModel = new NavMenuModel();

        $intId = $this->request->param("id", 0, "intval");

        if (empty($intId)) {
            $this->error(lang("NO_ID"));
        }


        $count = $navMenuModel->where(["parent_id" => $intId])->count();
        if ($count > 0) {
            $this->error("该菜单下还有子菜单，无法删除！");
        }


        $navMenuModel->where(["id" => $intId])->delete();
        $this->success(lang("DELETE_SUCCESS"), url("NavMenu/index"));

    }

    /**
     * 排序
     */
    public function listOrder()
    {
        $navMenuModel = new NavMenuModel();
        $status       = parent::listOrders($navMenuModel);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }


}