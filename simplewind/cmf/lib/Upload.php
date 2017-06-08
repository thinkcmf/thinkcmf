<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\lib;

use think\File;
use app\user\model\AssetModel;

/**
 * ThinkCMF上传类,分块上传
 */
class Upload
{
    private $request;
    private $error = false;
    private $fileType;
    private $formName = 'file';

    public function __construct()
    {
        $this->request = request();
    }

    public function getError()
    {
        return $this->error;
    }

    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
    }

    public function setFormName($name)
    {
        $this->formName = $name;
    }

    public function upload()
    {
        $uploadSetting = cmf_get_upload_setting();

        $arrFileTypes = [
            'image' => ['title' => 'Image files', 'extensions' => $uploadSetting['file_types']['image']['extensions']],
            'video' => ['title' => 'Video files', 'extensions' => $uploadSetting['file_types']['video']['extensions']],
            'audio' => ['title' => 'Audio files', 'extensions' => $uploadSetting['file_types']['audio']['extensions']],
            'file'  => ['title' => 'Custom files', 'extensions' => $uploadSetting['file_types']['file']['extensions']]
        ];

        $arrData = $this->request->param();
        if (empty($arrData["filetype"])) {
            $arrData["filetype"] = "image";
        }

        $fileType = $this->fileType;
        if (empty($this->fileType)) {
            $fileType = $arrData["filetype"];
        }

        if (array_key_exists($arrData["filetype"], $arrFileTypes)) {
            $extensions                = $uploadSetting['file_types'][$arrData["filetype"]]['extensions'];
            $fileTypeUploadMaxFileSize = $uploadSetting['file_types'][$fileType]['upload_max_filesize'];
        } else {
            $this->error = '上传文件类型配置错误！';
            return false;
        }

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

        $app = $this->request->post('app');
        if (!file_exists(APP_PATH . $app)) {
            $app = 'default';
        }

        $fileImage    = $this->request->file($this->formName);
        $originalName = $fileImage->getInfo('name');

        $arrAllowedExtensions = explode(',', $arrFileTypes[$fileType]['extensions']);

        $strFileExtension = strtolower(cmf_get_file_extension($originalName));

        if (!in_array($strFileExtension, $arrAllowedExtensions)) {
            $this->error = "非法文件类型！";
            return false;
        }

        $fileUploadMaxFileSize = $uploadSetting['upload_max_filesize'][$strFileExtension];
        $fileUploadMaxFileSize = empty($fileUploadMaxFileSize) ? 2097152 : $fileUploadMaxFileSize;//默认2M

        $strWebPath = "";//"upload" . DS;
        $strId      = $this->request->post("id");
        $strDate    = date('Ymd');

        $adminId   = cmf_get_current_admin_id();
        $userId    = cmf_get_current_user_id();
        $userId    = empty($adminId) ? $userId : $adminId;
        $targetDir = RUNTIME_PATH . "upload" . DS . $userId . DS; // 断点续传 need
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }


        /**
         * 断点续传 need
         */
        $strFilePath = md5($originalName);
        $chunk       = $this->request->param("chunk", 0, "intval");// isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks      = $this->request->param("chunks", 1, "intval");//isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;

        if (!$fileImage->isValid()) {
            $this->error = "非法文件！";
            return false;
        }

        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                $this->error = "Failed to open temp directory！";
                return false;
            }

            while (($file = readdir($dir)) !== false) {
                $tmpFilePath = $targetDir . $file;
                if ($tmpFilePath == "{$strFilePath}_{$chunk}.part" || $tmpFilePath == "{$strFilePath}_{$chunk}.parttmp") {
                    continue;
                }
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpFilePath) < time() - $maxFileAge)) {
                    @unlink($tmpFilePath);
                }
            }
            closedir($dir);
        }

        // Open temp file
        if (!$out = @fopen($targetDir . "{$strFilePath}_{$chunk}.parttmp", "wb")) {
            $this->error = "上传文件临时目录不可写" . $targetDir;
            return false;
        }
        // Read binary input stream and append it to temp file
        if (!$in = @fopen($fileImage->getInfo("tmp_name"), "rb")) {
            $this->error = "Failed to open input stream！";
            return false;
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

        if (!$done) {
            die('');//分片没上传完
        }

        $fileSaveName    = (empty($app) ? '' : $app . '/') . $strDate . '/' . md5(uniqid()) . "." . $strFileExtension;
        $strSaveFilePath = './upload/' . $fileSaveName; //TODO 测试 windows 下
        $strSaveFileDir  = dirname($strSaveFilePath);
        if (!file_exists($strSaveFileDir)) {
            mkdir($strSaveFileDir, 0777, true);
        }

        // 合并临时文件
        if (!$out = @fopen($strSaveFilePath, "wb")) {
            $this->error = "上传目录不可写";
            return false;
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

        $fileImage = new File($strSaveFilePath, 'r');
        $arrInfo   = [
            "name"     => $originalName,
            "type"     => $fileImage->getMime(),
            "tmp_name" => $strSaveFilePath,
            "error"    => 0,
            "size"     => $fileImage->getSize(),
        ];

        $fileImage->setSaveName($fileSaveName);
        $fileImage->setUploadInfo($arrInfo);

        /**
         * 断点续传 end
         */

        if (!$fileImage->validate(['size' => $fileUploadMaxFileSize])->check()) {
            $error = $fileImage->getError();
            unset($fileImage);
            unlink($strSaveFilePath);
            $this->error = $error;
            return false;
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

            if (empty($fileImage)) {
                $this->error = $fileImage->getError();
                return false;
            } else {
                $arrInfo["user_id"]     = $userId;
                $arrInfo["file_size"]   = $fileImage->getSize();
                $arrInfo["create_time"] = time();
                $arrInfo["file_md5"]    = md5_file($strSaveFilePath);
                $arrInfo["file_sha1"]   = sha1_file($strSaveFilePath);
                $arrInfo["file_key"]    = $arrInfo["file_md5"] . md5($arrInfo["file_sha1"]);
                $arrInfo["filename"]    = $fileImage->getInfo("name");
                $arrInfo["file_path"]   = $strWebPath . $fileSaveName;
                $arrInfo["suffix"]      = $fileImage->getExtension();
            }

        }

        //检查文件是否已经存在
        $assetModel = new AssetModel();
        $objAsset   = $assetModel->where(["user_id" => $userId, "file_key" => $arrInfo["file_key"]])->find();
        if ($objAsset) {
            $arrAsset = $objAsset->toArray();
            //$arrInfo["url"] = $this->request->domain() . $arrAsset["file_path"];
            $arrInfo["file_path"] = $arrAsset["file_path"];
            if (file_exists('./upload/' . $arrInfo["file_path"])) {
                @unlink($strSaveFilePath); // 删除已经上传的文件
            } else {
                rename($strSaveFilePath, './upload/' . $arrInfo["file_path"]);
            }

        } else {
            $assetModel->data($arrInfo)->allowField(true)->save();
        }

        //删除临时文件
//        for ($index = 0; $index < $chunks; $index++) {
//            // echo $targetDir . "{$strFilePath}_{$index}.part";
//            @unlink($targetDir . "{$strFilePath}_{$index}.part");
//        }
        @rmdir($targetDir);

        $storage = cmf_get_option('storage');

        if (empty($storage['type'])) {
            $storage['type'] = 'Local';
        }

        if ($storage['type'] != 'Local') { //  增加存储驱动
            $storage = new Storage($storage['type'], $storage['storages'][$storage['type']]);
            $result  = $storage->upload($arrInfo["file_path"], './upload/' . $arrInfo["file_path"], $fileType);

            if (!empty($result)) {
                return array_merge([
                    'filepath'    => $arrInfo["file_path"],
                    "name"        => $arrInfo["filename"],
                    'id'          => $strId,
                    'preview_url' => cmf_get_root() . '/upload/' . $arrInfo["file_path"],
                    'url'         => cmf_get_root() . '/upload/' . $arrInfo["file_path"],
                ], $result);
            }
        }

        return [
            'filepath'    => $arrInfo["file_path"],
            "name"        => $arrInfo["filename"],
            'id'          => $strId,
            'preview_url' => cmf_get_root() . '/upload/' . $arrInfo["file_path"],
            'url'         => cmf_get_root() . '/upload/' . $arrInfo["file_path"],
        ];
    }

}