<?php

/**
 * 附件上传
 */
namespace app\asset\controller;

use app\asset\model\AssetModel;
use cmf\controller\AdminBaseController;
use think\File;
use think\Request;

/**
 * 附件上传控制器
 * Class Asset
 * @package app\asset\controller
 */
class AssetController extends AdminBaseController
{


    function _initialize()
    {
        function _initialize()
        {
            $adminId = cmf_get_current_admin_id();
            $userId  = cmf_get_current_user_id();
            if (empty($adminId) && empty($userId)) {
                exit("非法上传！");
            }
        }
    }

    /**
     * webuploader 上传
     */
    public function webuploader()
    {

        $upload_setting = cmf_get_upload_setting();

        $arrFileTypes = [
            'image' => ['title' => 'Image files', 'extensions' => $upload_setting['image']['extensions']],
            'video' => ['title' => 'Video files', 'extensions' => $upload_setting['video']['extensions']],
            'audio' => ['title' => 'Audio files', 'extensions' => $upload_setting['audio']['extensions']],
            'file'  => ['title' => 'Custom files', 'extensions' => $upload_setting['file']['extensions']]
        ];

        if ($this->request->isPost()) {

            //$strPostMaxSize       = ini_get("post_max_size");
            //$strUploadMaxFileSize = ini_get("upload_max_filesize");

            /**
             * 断点续传 need
             */
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header("Access-Control-Allow-Origin: *"); // Support CORS

            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { // other CORS headers if any...
                exit; // finish preflight CORS requests here
            }

            @set_time_limit(10 * 60);
            $cleanupTargetDir = true; // Remove old files
            $maxFileAge       = 5 * 3600; // Temp file age in seconds

            /**
             * 断点续传 end
             */


            $fileImage       = $this->request->file("file");
            $strWebPath      = $this->request->root() . DS . "upload" . DS;
            $strSaveFilePath = ROOT_PATH . 'public' . DS . "upload" . DS;
            $strId           = $this->request->post("id");
            $strDate         = date('Ymd');

            $adminId   = cmf_get_current_admin_id();
            $userId    = cmf_get_current_user_id();
            $userId    = empty($adminId) ? $userId : $adminId;
            $targetDir = RUNTIME_PATH . "upload" . DS . $userId . DS; // 断点续传 need
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $strSaveFilePath = $strSaveFilePath.$strDate .DS;
            if (!file_exists($strSaveFilePath)) {
                mkdir($strSaveFilePath, 0777, true);
            }

            $arrAllowedExts = [];
            foreach ($arrFileTypes as $mfiletype) {
                array_push($arrAllowedExts, $mfiletype['extensions']);
            }

            $arrAllowedExts = implode(',', $arrAllowedExts);
            $arrAllowedExts = explode(',', $arrAllowedExts);
            $arrAllowedExts = array_unique($arrAllowedExts);

            $strFileExtension     = cmf_get_file_extension($_FILES['file']['name']);
            $intUploadMaxFileSize = $upload_setting['upload_max_filesize'][$strFileExtension];
            $intUploadMaxFileSize = empty($intUploadMaxFileSize) ? 2097152 : $intUploadMaxFileSize;//默认2M


            /**
             * 断点续传 need
             */
            $strFilePath = $fileImage->getInfo("name");
            $chunk       = $this->request->param("chunk", 0, "intval");// isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks      = $this->request->param("chunks", 1, "intval");//isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;

            
            if (!$fileImage->isValid()) {
                $arrResponse = [];
                $arrResponse["jsonrpc"]    = '2.0';
                $arrResponse["error"]      = '"code": 101, "message": "非法文件！"';
                $arrResponse["id"]    = $strId;
                die(json_encode($arrResponse));

            }
            if (!$fileImage->checkExt($arrAllowedExts)) {
                $arrResponse = [];
                $arrResponse["jsonrpc"]    = '2.0';
                $arrResponse["error"]      = '"code": 101, "message": "文件类型不正确！"';
                $arrResponse["id"]    = $strId;
                die(json_encode($arrResponse));

            }

            if ($cleanupTargetDir) {
                if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                    $arrResponse = [];
                    $arrResponse["jsonrpc"]    = '2.0';
                    $arrResponse["error"]      = '"code": 100, "message": "Failed to open temp directory！"';
                    $arrResponse["id"]    = $strId;
                    die(json_encode($arrResponse));

                }

                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir . $file;
                    if ($tmpfilePath == "{$strFilePath}_{$chunk}.part" || $tmpfilePath == "{$strFilePath}_{$chunk}.parttmp") {
                        continue;
                    }
                    if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                        @unlink($tmpfilePath);
                    }
                }
                closedir($dir);
            }

            // Open temp file
            if (!$out = @fopen($targetDir . "{$strFilePath}_{$chunk}.parttmp", "wb")) {
                $arrResponse = [];
                $arrResponse["jsonrpc"]    = '2.0';
                $arrResponse["error"]      = '"code": 102, "message": "Failed to open output stream！"';
                $arrResponse["id"]    = $strId;
                die(json_encode($arrResponse));

            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($fileImage->getInfo("tmp_name"), "rb")) {
                $arrResponse = [];
                $arrResponse["jsonrpc"]    = '2.0';
                $arrResponse["error"]      = '"code": 101, "message": "Failed to open input stream！"';
                $arrResponse["id"]    = $strId;
                die(json_encode($arrResponse));

            }

            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }

            @fclose($out);
            @fclose($in);

            rename($targetDir . "{$strFilePath}_{$chunk}.parttmp", $targetDir . "{$strFilePath}_{$chunk}.part");




            $done = true;
            for ($index = 0; $index < $chunks; $index++) {
                if (!file_exists($targetDir . "{$strFilePath}_{$index}.part")) {
                    $done = false;
                    break;
                }
            }
            if ($done) {

                $savename = $strSaveFilePath . md5(microtime(true)).".".pathinfo($strFilePath, PATHINFO_EXTENSION);

                if (!$out = @fopen($savename, "wb")) {
                    //mkdir($uploadPath);
                    $arrResponse = [];
                    $arrResponse["jsonrpc"]    = '2.0';
                    $arrResponse["error"]      = '"code": 121, "message": "Failed to open output stream！"';
                    $arrResponse["id"]    = $strId;
                    die(json_encode($arrResponse));

                }

                if (flock($out, LOCK_EX)) {
                    for ($index = 0; $index < $chunks; $index++) {
                        if (!$in = @fopen($targetDir . "{$strFilePath}_{$index}.part", "rb")) {
                            break;
                        }

                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }

                        @fclose($in);
                        @unlink("{$strFilePath}_{$index}.part");
                    }

                    flock($out, LOCK_UN);
                }

                @fclose($out);

                $fileImage = new File($savename,'r');
                $arrInfo   = ["name"  => $fileImage->getFilename(),
                              "type"  => $fileImage->getMime(),
                              "tmp_name"=>$strSaveFilePath . $strFilePath,
                              "error" => 0,
                              "size"  => $fileImage->getSize(),
                ];

                $fileImage->isTest(true);
                $fileImage->setSaveName( $strDate.DS.$fileImage->getFilename());
                $fileImage->setUploadInfo($arrInfo);

            } else {
                die();// die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "miss chunk"}, "id" : "'.$strId.'"}') ;
            }


            /**
             * 断点续传 end
             */

            if (!$fileImage->validate(['size' => $intUploadMaxFileSize * 1024, 'ext' => $arrAllowedExts])->check()) {
                $erorr =  $fileImage->getError();
                unset($fileImage);
                unlink($savename);
                $arrResponse = [];
                $arrResponse["jsonrpc"]    = '2.0';
                $arrResponse["error"]      = '"code": 101, "message": "' . $erorr. '"';
                $arrResponse["id"]    = $strId;
                die(json_encode($arrResponse));

            }

            //  $url=$first['url'];
            $storageSetting = cmf_get_cmf_settings('storage');
            $qiniuSetting   = $storageSetting['Qiniu']['setting'];
            //$url=preg_replace('/^https/', $qiniu_setting['protocol'], $url);
            //$url=preg_replace('/^http/', $qiniu_setting['protocol'], $url);

            $arrInfo = [];
            if (config('FILE_UPLOAD_TYPE') == 'Qiniu' && $qiniuSetting['enable_picture_protect']) {
                //todo  qiniu code ...
                // $previewUrl = $url.$qiniuSetting['style_separator'].$qiniuSetting['styles']['thumbnail300x300'];
                // $url= $url.$qiniuSetting['style_separator'].$qiniuSetting['styles']['watermark'];
            } else {

                //$info = $fileImage->move($strSaveFilePath);//开始上传

                if (!$fileImage) {
                    $arrResponse = [];
                    $arrResponse["jsonrpc"]    = '2.0';
                    $arrResponse["error"]      = '"code": 100, "message": "' . $fileImage->getError() . '"';
                    $arrResponse["id"]    = $strId;
                    die(json_encode($arrResponse));

                } else {
                    $arrInfo["url"]         = $this->request->domain() . $strWebPath . $fileImage->getSaveName();
                    $arrInfo["SaveName"]    = $fileImage->getFilename();
                    $arrInfo["user_id"]     = $userId;
                    $arrInfo["file_size"]   = $fileImage->getSize();
                    $arrInfo["create_time"] = time();
                    $arrInfo["file_md5"]    = md5_file($strSaveFilePath . $fileImage->getFilename());
                    $arrInfo["file_sha1"]   = sha1_file($strSaveFilePath . $fileImage->getFilename());
                    $arrInfo["file_key"]    = $arrInfo["file_md5"] . md5($arrInfo["file_sha1"]);
                    $arrInfo["filename"]    = $fileImage->getInfo("name");
                    $arrInfo["file_path"]   = $strWebPath . $fileImage->getSaveName();
                    $arrInfo["suffix"]      = $fileImage->getExtension();

                }

            }


            //检查文件是否已经存在
            $assetModel = new AssetModel();
            $objAsset   = $assetModel->where(["user_id" => $userId, "file_key" => $arrInfo["file_key"]])->find();
            if ($objAsset) {
                $arrAsset       = $objAsset->toArray();
                $arrInfo["url"] = $this->request->domain() . $arrAsset["file_path"];
                @unlink($strSaveFilePath . $arrInfo["SaveName"]);
            } else {
                $assetModel->data($arrInfo)->allowField(true)->save();
            }
            $arrResponse = [];
            $arrResponse["jsonrpc"]    = '2.0';
            $arrResponse["result"]      = $arrInfo["url"];
            $arrResponse["id"]    = $strId;
            $arrResponse["name"] =  $arrInfo["filename"];

            die(json_encode($arrResponse));


        } else {
            $arrMimeType = [];
            $arrData     = $this->request->param();
            if (empty($arrData["filetype"])) {
                $arrData["filetype"] = "image";
            }

            if (array_key_exists($arrData["filetype"], $arrFileTypes)) {
                $arrMimeType          = $arrFileTypes[$arrData["filetype"]];
                $intUploadMaxFileSize = $upload_setting[$arrData["filetype"]]['upload_max_filesize'];
                $extensions           = $upload_setting[$arrData["filetype"]]['extensions'];
            } else {
                $this->error('上传文件类型配置错误！');
            }
            $this->assign('filetype', $arrData["filetype"]);
            $this->assign('extensions', $extensions);
            $this->assign('upload_max_filesize', $intUploadMaxFileSize * 1024);
            $this->assign('upload_max_filesize_mb', intval($intUploadMaxFileSize / 1024));
            //$this->assign('mime_type',json_encode($arrMimeType));
//            if($arrData["multi"]<1){
//                $arrData["multi"] = 1;
//            }elseif($arrData["multi"]==1) {
//                $arrData["multi"] = 10;
//            }
            $this->assign('maxUp', $arrData["multi"] ? 5 : 1);
            $this->assign('multi', $arrData["multi"]);
            $this->assign('app', $arrData["app"]);
            //$this->assign("module",$this->request->param("module"));

            return $this->fetch(":webuploader");

        }
    }

}
