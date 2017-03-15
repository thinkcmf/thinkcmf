<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author:kane < chengjin005@163.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use app\portal\model\PortalTagModel;
use cmf\controller\AdminBaseController;
use app\portal\model\PortalPostModel;


/**
 * Class AdminTagController 标签管理控制器
 * @package app\portal\controller
 */
class AdminTagController extends AdminBaseController
{


    /**
     * 标签管理列表
     * @return mixed
     */
    public function index()
    {
        $portalTagModel =  new PortalTagModel();
        $objResult      =  $portalTagModel->select();

        $this->assign("arrStatus",$portalTagModel::$STATUS);
        $this->assign( "arrData" , $objResult?$objResult->toArray():array());
        return $this->fetch();
    }

    /**
     * 添加标签
     * @return mixed
     */
    public function add()
    {
        $portalTagModel = new PortalTagModel();
        $this->assign("arrStatus",$portalTagModel::$STATUS);
        return $this->fetch();
    }

    /**
     * 添加标签提交保存
     */
    public function addPost()
    {

        $arrData = $this->request->param();

        $portalTagModel = new PortalTagModel();
        $portalTagModel->isUpdate(false)->allowField(true)->save($arrData);

        $this->success(lang("SAVE_SUCCESS"));

    }

    /**
     * 更新标签状态提交保存
     */
    public function upStatus()
    {
        $intId     = $this->request->param("id");
        $intItatus = $this->request->param("status");
        $intItatus = $intItatus?1:0;
        if(empty($intId))
        {
            $this->error(lang("NO_ID"));
        }


        $portalTagModel = new PortalTagModel();
        $portalTagModel->isUpdate(true)->save(["status"=>$intItatus],["id"=>$intId]);

        $this->success(lang("SAVE_SUCCESS"));

    }

    /**
     * 删除标签
     */
    public function delete()
    {

        $intId     = $this->request->param("id");

        if(empty($intId))
        {
            $this->error(lang("NO_ID"));
        }
        $portalTagModel = new PortalTagModel();


        $portalTagModel->where(['id' => $intId])->delete();
        $this->success(lang("DELETE_SUCCESS"));




    }
}
