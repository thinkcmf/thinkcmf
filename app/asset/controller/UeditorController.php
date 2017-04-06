<?php
namespace app\asset\controller;

use app\asset\model\AssetModel;
use cmf\controller\HomeBaseController;
use think\File;
use think\Request;

/**
 * 百度编辑器文件上传处理控制器
 * Class Ueditor
 * @package app\asset\controller
 */
class UeditorController extends HomeBaseController
{


    private $stateMap = [ //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE"           => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED"        => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED"   => "文件类型不允许",
        "ERROR_CREATE_DIR"         => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE"  => "目录没有写权限",
        "ERROR_FILE_MOVE"          => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND"     => "找不到上传文件",
        "ERROR_WRITE_CONTENT"      => "写入文件内容错误",
        "ERROR_UNKNOWN"            => "未知错误",
        "ERROR_DEAD_LINK"          => "链接不可用",
        "ERROR_HTTP_LINK"          => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE"   => "链接contentType不正确"
    ];

    /**
     * 初始化
     */
    function _initialize()
    {
        $adminId = cmf_get_current_admin_id();
        $userId  = cmf_get_current_user_id();
        if (empty($adminId) && empty($userId)) {
            exit("非法上传！");
        }
    }

    /**
     * 处理上传处理
     */
    function upload()
    {
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        $action = $this->request->param('action');

        switch ($action) {

            case 'config':
                $result = $this->ueditorConfig();
                break;

            /* 上传图片 */
            case 'uploadimage':
                $result = $this->ueditorUpload("image");
                break;
            /* 上传涂鸦 */
            case 'uploadscrawl':
                $result = $this->ueditorUpload("image");
                break;
            /* 上传视频 */
            case 'uploadvideo':
                $result = $this->ueditorUpload("video");
                break;
            /* 上传文件 */
            case 'uploadfile':
                $result = $this->ueditorUpload("file");
                break;

            /* 列出图片 */
            case 'listimage':
                $result = "";
                break;
            /* 列出文件 */
            case 'listfile':
                $result = "";
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->_get_remote_image();
                break;

            default:
                $result = json_encode(['state' => '请求地址出错']);
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"]) && false) {//TODO 跨域上传
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode([
                    'state' => 'callback参数不合法'
                ]);
            }
        } else {
            exit($result);
        }
    }


    /**
     * 获取远程图片
     */
    private function _get_remote_image()
    {

        $source = $this->request->param('source');


        $item = [
            "state"    => "",
            "url"      => "",
            "size"     => "",
            "title"    => "",
            "original" => "",
            "source"   => ""
        ];
        $date = date("Ymd");
        $uploadSetting     = cmf_get_upload_setting();
        $uploadMaxFileSize = $uploadSetting["image"]['upload_max_filesize'];
        $uploadMaxFileSize = empty($uploadMaxFileSize) ? 2048 : $uploadMaxFileSize;//默认2M
        $allowedExts = explode(',', $uploadSetting["image"]["extensions"]);
        $strSavePath = ROOT_PATH . 'public'.DS . "ueditor".DS .$date.DS;
        //远程抓取图片配置
        $config = [
            "savePath"   =>$strSavePath ,            //保存路径
            "allowFiles" =>$allowedExts,// [".gif", ".png", ".jpg", ".jpeg", ".bmp"], //文件允许格式
            "maxSize"    => $uploadMaxFileSize                    //文件大小限制，单位KB
        ];

        $storage_setting = cmf_get_cmf_settings('storage');
        $qiniu_domain    = $storage_setting['Qiniu']['domain'];
        $no_need_domains = [$qiniu_domain];

        $list = [];
        foreach ($source as $imgUrl) {
            $host = str_replace(['http://', 'https://'], '', $imgUrl);
            $host = explode('/', $host);
            $host = $host[0];
            if (in_array($host, $no_need_domains)) {
                continue;
            }
            $return_img           = $item;
            $return_img['source'] = $imgUrl;
            $imgUrl               = htmlspecialchars($imgUrl);
            $imgUrl               = str_replace("&amp;", "&", $imgUrl);
            //http开头验证
            if (strpos($imgUrl, "http") !== 0) {
                $return_img['state'] = $this->stateMap['ERROR_HTTP_LINK'];
                array_push($list, $return_img);
                continue;
            }

            //获取请求头
           // is_sae()

            if (!cmf_is_sae()) {//SAE下无效
                $heads = get_headers($imgUrl);
                //死链检测
                if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
                    $return_img['state'] = $this->stateMap['ERROR_DEAD_LINK'];
                    array_push($list, $return_img);
                    continue;
                }
            }

            //格式验证(扩展名验证和Content-Type验证)
            $fileType = strtolower(strrchr($imgUrl, '.'));
            if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
                $return_img['state'] = $this->stateMap['ERROR_HTTP_CONTENTTYPE'];
                array_push($list, $return_img);
                continue;
            }

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                [
                    'http' => [
                        'follow_location' => false // don't follow redirects
                    ]
                ]
            );
            //请确保php.ini中的fopen wrappers已经激活
            readfile($imgUrl, false, $context);
            $img = ob_get_contents();
            ob_end_clean();

            //大小验证
            $uriSize   = strlen($img); //得到图片大小
            $allowSize = 1024 * $config['maxSize'];
            if ($uriSize > $allowSize) {
                $return_img['state'] = $this->stateMap['ERROR_SIZE_EXCEED'];
                array_push($list, $return_img);
                continue;
            }

            $file     = uniqid() . strrchr($imgUrl, '.');
            $savePath = $config['savePath'];
            $tmpName  = $savePath . $file;

            //创建保存位置
            if (!file_exists($savePath)) {
                mkdir("$savePath", 0777, true);
            }

            $file_write_result = cmf_file_write($tmpName, $img);

            if ($file_write_result) {
                if (config('FILE_UPLOAD_TYPE') == 'Qiniu') {

                   //todo qiniu  code

                }

                if (config('FILE_UPLOAD_TYPE') == 'Local') {

                    $file = $strSavePath . $file;

                    $return_img['state'] = 'SUCCESS';
                    $return_img['url']   = $file;
                    array_push($list, $return_img);
                }
            } else {
                $return_img['state'] = $this->stateMap['ERROR_WRITE_CONTENT'];
                array_push($list, $return_img);
            }
        }

        return json_encode([
            'state' => count($list) ? 'SUCCESS' : 'ERROR',
            'list'  => $list
        ]);
    }

    /**
     * 文件上传
     * @param string $fileType 文件类型
     * @return string
     */
    private function ueditorUpload($fileType = 'image')
    {

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



        $uploadSetting     = cmf_get_upload_setting();
        $fileImage         = $this->request->file("upfile");
        $fileExtension     = $fileImage->getExtension();//  cmf_get_file_extension($_FILES['upfile']['name']);
        $uploadMaxFileSize = $uploadSetting[$fileType]['upload_max_filesize']*1024;
        $uploadMaxFileSize = empty($uploadMaxFileSize) ? 2097152 : $uploadMaxFileSize;//默认2M

        $adminId = cmf_get_current_admin_id();
        $userId  = cmf_get_current_user_id();
        $userId  = empty($adminId) ? $userId : $adminId;
        $strId   = $this->request->post("id");


        $allowedExts = explode(',', $uploadSetting[$fileType]["extensions"]);
        $strWebPath  = $this->request->root() . DS . "upload" . DS . "ueditor" . DS;
        $strSaveFilePath = ROOT_PATH . 'public' . DS . "upload" . DS . "ueditor" . DS;
        $targetDir = RUNTIME_PATH . "upload" . DS . "ueditor" . DS. $userId . DS; // 断点续传 need
        $strDate   = date('Ymd');
        if (!file_exists(RUNTIME_PATH . "upload" . DS . "ueditor" . DS)) {
            mkdir(RUNTIME_PATH . "upload" . DS . "ueditor" . DS, 0777, true);
        }
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $strSaveFilePath = $strSaveFilePath.$strDate .DS;
        if (!file_exists($strSaveFilePath)) {
            mkdir($strSaveFilePath, 0777, true);
        }

        /**
         * 断点续传 need
         */
        $strFilePath = $fileImage->getInfo("name");// md5($fileImage->getInfo("name"));
        $chunk       = $this->request->param("chunk", 0, "intval");// isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks      = $this->request->param("chunks", 1, "intval");//isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        $arrReturn   = [];
        if (!$fileImage->isValid()) {
            $arrReturn['state'] = "非法文件!";
            return json_encode($arrReturn) ;
            //die ('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "非法文件！"}, "id" : "' . $strId . '"}');
        }
        if (!$fileImage->checkExt($allowedExts)) {
            $arrReturn['state'] = "文件类型不正确!";
            return json_encode($arrReturn) ;
            //die ('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "文件类型不正确！"}, "id" : "' . $strId . '"}');
        }

        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                $arrReturn['state'] = "Failed to open temp directory";
                return json_encode($arrReturn) ;
                //die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "' . $strId . '"}');
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
            $arrReturn['state'] = "Failed to open output stream.";
            return json_encode($arrReturn) ;
           // die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "' . $strId . '"}');
        }
        // Read binary input stream and append it to temp file
        if (!$in = @fopen($fileImage->getInfo("tmp_name"), "rb")) {
            $arrReturn['state'] = "Failed to open output stream.";
            return json_encode($arrReturn) ;
            //die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "' . $strId . '"}');

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
                $arrReturn['state'] = "Failed to open output stream.";
                die(json_encode($arrReturn)) ;
                //mkdir($uploadPath);
                //die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "' . $strId . '"}');
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

            $fileImage = new File($savename);
            $arrInfo   = ["name"  => $fileImage->getFilename(),
                          "type"  => $fileImage->getMime(),
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





        if (!$fileImage->validate(['size' => $uploadMaxFileSize, 'ext' => $allowedExts])->check()) {
            $arrReturn['state'] = $fileImage->getError();
            unset($fileImage);
            unlink($savename);
            return json_encode($arrReturn);
        }

        $storageSetting = cmf_get_cmf_settings('storage');
        $qiniuSetting   = $storageSetting['Qiniu']['setting'];
        $arrInfo        = [];
        if (config('FILE_UPLOAD_TYPE') == 'Qiniu' && $qiniuSetting['enable_picture_protect']) {
            //todo  qiniu code ...

        } else {
            //$info = $fileImage->move($strSaveFilePath);//开始上传

            if (!$fileImage) {
                $arrReturn['state'] = $fileImage->getError();
                return json_encode($arrReturn);
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
        $arrResponse["state"]    = 'SUCCESS';
        $arrResponse["url"]      = $arrInfo["url"];
        $arrResponse["title"]    = $arrInfo["filename"];
        $arrResponse["original"] = $arrInfo["filename"];


        return json_encode($arrResponse);
    }

    /**
     * 获取百度编辑器配置
     */
    private function ueditorConfig()
    {

        $config_text    = preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("./static/js/ueditor/config.json"));
        $config         = json_decode($config_text, true);
        $upload_setting = cmf_get_upload_setting();

        $config['imageMaxSize']    = $upload_setting['image']['upload_max_filesize'] * 1024;
        $config['imageAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['image']['extensions']));
        $config['scrawlMaxSize']   = $upload_setting['image']['upload_max_filesize'] * 1024;
//
        $config['catcherMaxSize']    = $upload_setting['image']['upload_max_filesize'] * 1024;
        $config['catcherAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['image']['extensions']));

        $config['videoMaxSize']    = $upload_setting['video']['upload_max_filesize'] * 1024;
        $config['videoAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['video']['extensions']));

        $config['fileMaxSize']    = $upload_setting['file']['upload_max_filesize'] * 1024;
        $config['fileAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['file']['extensions']));

        return json_encode($config);
    }

    /**
     * 格式化后缀
     * @param $str
     * @return string
     */
    private function ueditorExtension($str)
    {

        return "." . trim($str, '.');
    }

    /**
     * @function imageManager
     */
    public function imageManager()
    {

        header("Content-Type: text/html; charset=utf-8");
        //需要遍历的目录列表，最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
        $paths = array(C("TMPL_PARSE_STRING.__UPLOAD__"), 'upload/');


        $files = array();
        foreach ($paths as $path) {
                    $tmp = $this->getfiles($path);
                    if ($tmp) {
                        $files = array_merge($files, $tmp);
                    }
        }
        if (!count($files)) return;
        rsort($files, SORT_STRING);
        $str = "";
        foreach ($files as $file) {
                    $str .= ROOT_PATH . '/' . $file . "ue_separate_ue";
        }
        echo $str;


    }
    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    private function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = array(
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }
}