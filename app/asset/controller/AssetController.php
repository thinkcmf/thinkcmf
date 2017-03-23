<?php

/**
 * 附件上传
 */
namespace app\asset\controller;
use app\asset\model\AssetModel;
use cmf\controller\AdminBaseController;
use think\Request;

/**
 * 附件上传控制器
 * Class Asset
 * @package app\asset\controller
 */
class AssetController extends AdminBaseController {


    function _initialize() {
        function _initialize() {
            $adminid = cmf_get_current_admin_id();
            $userid  = cmf_get_current_userid();
            if(empty($adminid) && empty($userid)){
                exit("非法上传！");
            }
        }
    }

    /**
     * webuploader 上传
     */
    public function webuploader() {

        $upload_setting = cmf_get_upload_setting();

        $arrFileTypes=array(
            'image'=>array('title'=>'Image files','extensions'=>$upload_setting['image']['extensions']),
            'video'=>array('title'=>'Video files','extensions'=>$upload_setting['video']['extensions']),
            'audio'=>array('title'=>'Audio files','extensions'=>$upload_setting['audio']['extensions']),
            'file'=>array('title'=>'Custom files','extensions'=>$upload_setting['file']['extensions'])
        );

        if ($this->request->isPost()) {




            //$intPostMaxSize       = ini_get("post_max_size");
            //$intUploadMaxFileSize = ini_get("upload_max_filesize");


            $fileImage   = $this->request->file("file");
            $strWebPath  = $this->request->root() . DS."up_files". DS;
            $strFilePath = ROOT_PATH . 'public' . DS."up_files". DS ;
            $strId       = $this->request->post("id");

            $adminid = cmf_get_current_admin_id();
            $userid  = cmf_get_current_userid();
            $userid  = empty($adminid)?$userid:$adminid;
            //$targetDir = $strFilePath.DS."upload_tmp".DS.$userid.DS;
            if (!file_exists($strFilePath)) {
                @mkdir($strFilePath);
            }

            $arrAllowedExts = array();
            foreach ($arrFileTypes as $mfiletype){
                array_push($arrAllowedExts, $mfiletype['extensions']);
            }
            $arrAllowedExts = implode(',', $arrAllowedExts);
            $arrAllowedExts = explode(',', $arrAllowedExts);
            $arrAllowedExts = array_unique($arrAllowedExts);

            $strFileExtension = cmf_get_file_extension($_FILES['file']['name']);
            $intUploadMaxFileSize=$upload_setting['upload_max_filesize'][$strFileExtension];
            $intUploadMaxFileSize=empty($intUploadMaxFileSize)?2097152:$intUploadMaxFileSize;//默认2M




            if(!$fileImage->validate(['size'=>$intUploadMaxFileSize*1024,'ext'=>$arrAllowedExts])->check()) {
                die ('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "'.$fileImage->getError().'"}, "id" : "'.$strId.'"}') ;
            }





          //  $url=$first['url'];
            $storageSetting=cmf_get_cmf_settings('storage');
            $qiniuSetting=$storageSetting['Qiniu']['setting'];
            //$url=preg_replace('/^https/', $qiniu_setting['protocol'], $url);
            //$url=preg_replace('/^http/', $qiniu_setting['protocol'], $url);

            $arrInfo = array();
            if(config('FILE_UPLOAD_TYPE')=='Qiniu' && $qiniuSetting['enable_picture_protect']){
                //todo  qianniu code ...
               // $previewUrl = $url.$qiniuSetting['style_separator'].$qiniuSetting['styles']['thumbnail300x300'];
               // $url= $url.$qiniuSetting['style_separator'].$qiniuSetting['styles']['watermark'];
            }else{
                $info = $fileImage->move($strFilePath);//开始上传

                if(!$info)
                {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "'. $fileImage->getError().'"}, "id" : "'.$strId.'"}') ;
                }else{
                    $arrInfo["url"]         = $this->request->domain().$strWebPath.$info->getSaveName();
                    $arrInfo["SaveName"]    = $info->getSaveName();
                    $arrInfo["user_id"]     = $userid;
                    $arrInfo["file_size"]   = $info->getSize();
                    $arrInfo["create_time"] = time();
                    $arrInfo["file_md5"]    = md5_file($strFilePath.$info->getSaveName());
                    $arrInfo["file_sha1"]   = sha1_file($strFilePath.$info->getSaveName());
                    $arrInfo["file_key"]    = $arrInfo["file_md5"].md5($arrInfo["file_sha1"]);
                    $arrInfo["filename"]    = $info->getInfo("name");
                    $arrInfo["file_path"]   = $strWebPath.$info->getSaveName();
                    $arrInfo["suffix"]      = $info->getExtension();

                }

            }



            //检查文件是否已经存在
            $assetModel = new AssetModel();
            $objAsset   = $assetModel->where( ["user_id"=>$userid,"file_key"=>$arrInfo["file_key"]])->find();
            if($objAsset)
            {
                $arrAsset = $objAsset->toArray();
                $arrInfo["url"] = $this->request->domain().$arrAsset["file_path"];
                @unlink($strFilePath.$arrInfo["SaveName"] );
            }else{
                $assetModel->data($arrInfo)->allowField(true)->save();
            }

            die('{"jsonrpc" : "2.0", "result" : "'.$arrInfo["url"] .'", "id" : "'.$strId.'","name":"'.$arrInfo["filename"].'"}') ;

        } else {
            $arrMimeType = array();
            $arrData     = $this->request->param();
            if(empty($arrData["filetype"]))
            {
                $arrData["filetype"] = "image";
            }

            if(array_key_exists($arrData["filetype"], $arrFileTypes)){
                $arrMimeType          = $arrFileTypes[$arrData["filetype"]];
                $intUploadMaxFileSize = $upload_setting[$arrData["filetype"]]['upload_max_filesize'];
                $extensions           = $upload_setting[$arrData["filetype"]]['extensions'];
            }else{
                $this->error('上传文件类型配置错误！');
            }
            $this->assign('filetype',$arrData["filetype"]);
            $this->assign('extensions',$extensions);
            $this->assign('upload_max_filesize',$intUploadMaxFileSize*1024);
            $this->assign('upload_max_filesize_mb',intval($intUploadMaxFileSize/1024));
            //$this->assign('mime_type',json_encode($arrMimeType));
//            if($arrData["multi"]<1){
//                $arrData["multi"] = 1;
//            }elseif($arrData["multi"]==1) {
//                $arrData["multi"] = 10;
//            }
            $this->assign('maxUp',$arrData["multi"]?5:1);
            $this->assign('multi',$arrData["multi"]);
            $this->assign('app',$arrData["app"]);
            //$this->assign("module",$this->request->param("module"));

            return $this->fetch(":webuploader");

        }
    }

}
