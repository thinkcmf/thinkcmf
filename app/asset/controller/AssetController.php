<?php

/**
 * 附件上传
 */
namespace app\asset\controller;
use cmf\controller\AdminBaseController;
use think\Request;

/**
 * 附件上传控制器
 * Class Asset
 * @package app\asset\controller
 */
class AssetController extends AdminBaseController {


    function _initialize() {
    	$adminid=cmf_get_current_admin_id();
    	//$userid=sp_get_current_userid();
    	if(empty($adminid)){
    		exit("非法上传！");
    	}
    }

    /**
     * plupload 上传
     */
    public function plupload() {

        if ($this->request->isPost()) {

            $str_save_path = "";
            $strModule     = $this->request->param("module");

            if(!empty( $strModule ))
            {
                $userid = cmf_get_current_userid();
                if($strModule == "worker_master_register")
                {
                    $str_save_path = "/user_".$userid."/worker_master_register/";
                }
                if($strModule == "create_order")
                {
                    $str_save_path = "/user_".$userid."/create_order/";
                }
            }


            $fileImage = $this->request->file("file");

            if(empty($fileImage))
            {
                $this->error("file  不能为空！");
            }
            $strFilePath = ROOT_PATH . 'public'.config("view_replace_str.__UP__") . DS ;
            $info = $fileImage->validate(['size'=>2*1024*1024,'ext'=>'jpg,jpeg,gif,png,icon'])->move($strFilePath);


            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息

                $url = $this->request->domain().config("view_replace_str.__UP__").$info->getSaveName();

                die('{"jsonrpc" : "2.0", "result" : "'.$url.'", "id" : "id","name":"'.$info->getInfo("name").'"}');
//				echo "1," . $url.",".'1,'.$info->getInfo("name");
//				exit;
            } else {
                //上传失败，返回错误
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "'. $fileImage->getError().'"}, "id" : "id"}');

            }
        } else {
            if( in_array($this->request->param("module"),array("worker_master_register","create_order")) )
            {
                $this->assign("file_upload_limit",3);
            }
            $this->assign("file_upload_limit",1);
            $this->assign("module",$this->request->param("module"));
            return $this->fetch(":plupload");

        }
    }
    /**
     * swfupload 上传
     */
    public function swfupload() {

        if ($this->request->isPost()) {

            $str_save_path = "";
            $strModule     = $this->request->param("module");

            if(!empty( $strModule ))
            {
                $userid = cmf_get_current_userid();
                if($strModule == "worker_master_register")
                {
                    $str_save_path = "/user_".$userid."/worker_master_register/";
                }
                if($strModule == "create_order")
                {
                    $str_save_path = "/user_".$userid."/create_order/";
                }
            }


            $fileImage = $this->request->file("Filedata");

            if(empty($fileImage))
            {
                $this->error("icon  不能为空！");
            }
            $strFilePath = ROOT_PATH . 'public'.config("view_replace_str.__UP__") . DS ;
            $info = $fileImage->validate(['size'=>2*1024*1024,'ext'=>'jpg,jpeg,gif,png,icon'])->move($strFilePath);


            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息

                $url=config("view_replace_str.__UP__").$info->getSaveName();


                echo "1," . $url.",".'1,'.$info->getInfo("name");
                exit;
            } else {
                //上传失败，返回错误
                exit("0," . $fileImage->getError());
            }
        } else {
            if( in_array($this->request->param("module"),array("worker_master_register","create_order")) )
            {
                $this->assign("file_upload_limit",3);
            }
            $this->assign("file_upload_limit",1);
            $this->assign("module",$this->request->param("module"));
            return $this->fetch(":swfupload");

        }
    }
}
