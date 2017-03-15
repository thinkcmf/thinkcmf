<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
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
    // 导航列表
    public function index()
    {
        $navModel = new NavModel();

        $navs = $navModel->select();
        $this->assign('navs', $navs);

        return $this->fetch();

    }
    /**
     * 添加导航
     * @return mixed
     */

    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加导航提交保存
     */
    public function addPost()
    {

        $navModel    = new NavModel();
        $arrData     = $this->request->post();

        if(empty($arrData["is_main"]))
        {
            $arrData["is_main"] = 0;
        } else {
            $navModel->where("is_main",1)->update(array("is_main"=>0));
        }

        $navModel->allowField(true)->insert($arrData) ;
        $this->success(lang("EDIT_SUCCESS"), url("nav/index"));

    }

    /**
     * 编辑导航
     * @return mixed
     */

    public function edit()
    {
        $navModel    = new NavModel();
        $intId       = $this->request->param("id",0,'intval');

        $objNavCat   = $navModel->where(array("id"=>$intId))->find();
        $arrNavCat   = $objNavCat?$objNavCat->toArray():array();

        $this->assign($arrNavCat);
        return $this->fetch();
    }



    /**
     * 编辑导航提交保存
     */
    public function editPost()
    {

        $navModel    = new NavModel();
        $arrData     = $this->request->post();

        if(empty($arrData["is_main"]))
        {
            $arrData["is_main"] = 0;
        } else {
            $navModel->where("is_main",1)->update(array("is_main"=>0));
        }

        $navModel->allowField(true)->where(["id"=>$arrData["id"]])->update($arrData) ;
        $this->success(lang("EDIT_SUCCESS"), url("nav/index"));

    }

    /**
     * 删除导航
     */
    public function delete()
    {
        $navModel    = new NavModel();
        $intId       = $this->request->param("id",0,"intval");

        if(empty($intId))
        {
            $this->error(lang("NO_ID"));
        }

        $navModel->where(["id"=>$intId])->delete();
        $this->success(lang("DELETE_SUCCESS"), url("nav/index"));

    }



}