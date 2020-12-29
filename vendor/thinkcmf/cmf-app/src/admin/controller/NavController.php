<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: kane <chengjin005@163.com> 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\NavModel;

/**
 * Class NavController 导航类别管理控制器
 * @package app\admin\controller
 */
class NavController extends AdminBaseController
{
    /**
     * 导航管理
     * @adminMenu(
     *     'name'   => '导航管理',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 30,
     *     'icon'   => '',
     *     'remark' => '导航管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('admin_nav_index_view');

        if (!empty($content)) {
            return $content;
        }

        $navModel = new NavModel();

        $navs = $navModel->select();
        $this->assign('navs', $navs);

        return $this->fetch();

    }

    /**
     * 添加导航
     * @adminMenu(
     *     'name'   => '添加导航',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加导航',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加导航提交保存
     * @adminMenu(
     *     'name'   => '添加导航提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加导航提交保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $navModel = new NavModel();
            $arrData  = $this->request->post();

            if (empty($arrData["is_main"])) {
                $arrData["is_main"] = 0;
            } else {
                $navModel->where("is_main", 1)->update(["is_main" => 0]);
            }

            $navModel->insert($arrData);
            $this->success(lang("EDIT_SUCCESS"), url("Nav/index"));
        }

    }

    /**
     * 编辑导航
     * @adminMenu(
     *     'name'   => '编辑导航',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑导航',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $navModel = new NavModel();
        $intId    = $this->request->param("id", 0, 'intval');

        $objNavCat = $navModel->where("id", $intId)->find();
        $arrNavCat = $objNavCat ? $objNavCat->toArray() : [];

        $this->assign($arrNavCat);
        return $this->fetch();
    }


    /**
     * 编辑导航提交保存
     * @adminMenu(
     *     'name'   => '编辑导航提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑导航提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $navModel = new NavModel();
            $arrData  = $this->request->post();

            if (empty($arrData["is_main"])) {
                $arrData["is_main"] = 0;
            } else {
                $navModel->where("is_main", 1)->update(["is_main" => 0]);
            }

            $navModel->where("id", intval($arrData["id"]))->update($arrData);
            $this->success(lang("EDIT_SUCCESS"), url("nav/index"));
        }
    }

    /**
     * 删除导航
     * @adminMenu(
     *     'name'   => '删除导航',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除导航',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $navModel = new NavModel();
            $intId    = $this->request->param("id", 0, "intval");

            if (empty($intId)) {
                $this->error(lang("NO_ID"));
            }

            $navModel->where("id", $intId)->delete();
            $this->success(lang("DELETE_SUCCESS"), url("Nav/index"));
        }

    }


}
