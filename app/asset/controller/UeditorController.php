<?php
namespace app\asset\controller;
use app\asset\model\AssetModel;
use cmf\controller\HomeBaseController;
use think\Request;

/**
 * 百度编辑器文件上传处理控制器
 * Class Ueditor
 * @package app\asset\controller
 */
class UeditorController  extends HomeBaseController {
	
	
	private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确"
    );

    /**
     * 初始化
     */
	function _initialize() {
		$adminid=cmf_get_current_admin_id();
		$userid=cmf_get_current_userid();
		if(empty($adminid) && empty($userid)){
			exit("非法上传！");
		}
	}
	
	/**
     * @deprecated
	 * ueditor 1.3.6 upload img
	 */
	public function uploadimg(){

		$file = $title = $oriName = $state ='0';
        $fileImage = $this->request->file("upfile");

        if(empty($fileImage))
        {
            $this->error("icon  不能为空！");
        }
        $strFilePath = ROOT_PATH . 'public'.config("view_replace_str.__UP__") .DS."ueditor".DS ;
        $info = $fileImage->validate(['size'=>2*1024*1024,'ext'=>'jpg,jpeg,gif,png,icon'])->move($strFilePath);


        //开始上传
		if ($info) {

			//上传成功
			$title = $oriName = $info->getInfo("name");
			
			$state = 'SUCCESS';
			$file = config("view_replace_str.__UP__").DS."ueditor".DS .$info->getSaveName();;
			if(strpos($file, "https")===0 || strpos($file, "http")===0){
				
			}else{//local
				$host=(cmf_is_ssl() ? 'https' : 'http')."://".$_SERVER['HTTP_HOST'];
				$file=$host.$file;
			}
		} else {
			$state = $fileImage->getError();
		}
		$response= "{'url':'" .$file . "','title':'" . $title . "','original':'" . $oriName . "','state':'" . $state . "'}";
		exit($response);
	}

    /**
     * 团票管理
     */
	public function imageManager(){
		error_reporting(E_ERROR|E_WARNING);
		$path = 'upload'; //最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
		$action = htmlspecialchars($_POST["action"]);
		if($action=="get"){
			$files = $this->getfiles($path);
			if(!$files)return;
			$str = "";
			foreach ($files as $file) {
				$str .= $file."ue_separate_ue";
			}
			echo $str;
		}
	}
	
	//imageManager()用的到
	private function getfiles(){
		if (!is_dir($path)) return;
		
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . '/' . $file;
				if (is_dir($path2)) {
					getfiles($path2, $files);
				} else {
					if (preg_match("/\.(gif|jpeg|jpg|png|bmp)$/i", $file)) {
						$files[] = $path2;
					}
				}
			}
		}return $files;
	}
	


    /**
     * 处理上传处理
     */
	function upload(){


		//date_default_timezone_set("Asia/chongqing");
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
				$result=$this->_get_remote_image();
				break;
		
			default:
				$result = json_encode(array('state'=> '请求地址出错'));
				break;
		}
		
		/* 输出结果 */
		if (isset($_GET["callback"]) && false ) {//TODO 跨域上传
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				echo json_encode(array(
						'state'=> 'callback参数不合法'
				));
			}
		} else {
			exit($result) ;
		}
	}




    /**
     * 获取远程图片
     */
    private function _get_remote_image(){
        $source=array();
        if (isset($_POST['source'])) {
            $source = $_POST['source'];
        } else {
            $source = $_GET['source'];
        }

        $item=array(
            "state" => "",
            "url" => "",
            "size" => "",
            "title" => "",
            "original" => "",
            "source" =>""
        );
        $date=date("Ymd");
        //远程抓取图片配置
        $config = array(
            "savePath" => './'. C("UPLOADPATH")."ueditor/$date/",            //保存路径
            "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" ) , //文件允许格式
            "maxSize" => 3000                    //文件大小限制，单位KB
        );

        $storage_setting=sp_get_cmf_settings('storage');
        $qiniu_domain=$storage_setting['Qiniu']['domain'];
        $no_need_domains=array($qiniu_domain);

        $list = array();
        foreach ( $source as $imgUrl ) {
            $host=str_replace(array('http://','https://'), '', $imgUrl);
            $host=explode('/', $host);
            $host=$host[0];
            if(in_array($host, $no_need_domains)){
                continue;
            }
            $return_img=$item;
            $return_img['source']=$imgUrl;
            $imgUrl = htmlspecialchars($imgUrl);
            $imgUrl = str_replace("&amp;", "&", $imgUrl);
            //http开头验证
            if(strpos($imgUrl,"http")!==0){
                $return_img['state']=$this->stateMap['ERROR_HTTP_LINK'];
                array_push( $list , $return_img );
                continue;
            }

            //获取请求头
            if(!sp_is_sae()){//SAE下无效
                $heads = get_headers( $imgUrl );
                //死链检测
                if ( !( stristr( $heads[ 0 ] , "200" ) && stristr( $heads[ 0 ] , "OK" ) ) ) {
                    $return_img['state']=$this->stateMap['ERROR_DEAD_LINK'];
                    array_push( $list , $return_img );
                    continue;
                }
            }

            //格式验证(扩展名验证和Content-Type验证)
            $fileType = strtolower( strrchr( $imgUrl , '.' ) );
            if ( !in_array( $fileType , $config[ 'allowFiles' ] ) || stristr( $heads[ 'Content-Type' ] , "image" ) ) {
                $return_img['state']=$this->stateMap['ERROR_HTTP_CONTENTTYPE'];
                array_push( $list , $return_img );
                continue;
            }

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                array (
                    'http' => array (
                        'follow_location' => false // don't follow redirects
                    )
                )
            );
            //请确保php.ini中的fopen wrappers已经激活
            readfile( $imgUrl,false,$context);
            $img = ob_get_contents();
            ob_end_clean();

            //大小验证
            $uriSize = strlen( $img ); //得到图片大小
            $allowSize = 1024 * $config[ 'maxSize' ];
            if ( $uriSize > $allowSize ) {
                $return_img['state']=$this->stateMap['ERROR_SIZE_EXCEED'];
                array_push( $list , $return_img );
                continue;
            }

            $file=uniqid() . strrchr( $imgUrl , '.' );
            $savePath = $config[ 'savePath' ];
            $tmpName = $savePath .$file ;

            //创建保存位置
            if ( !file_exists( $savePath ) ) {
                mkdir( "$savePath" , 0777 ,true);
            }

            $file_write_result=sp_file_write($tmpName,$img);

            if($file_write_result){
                if(C('FILE_UPLOAD_TYPE')=='Qiniu'){
                    $upload = new \Think\Upload();
                    $savename="ueditor/$date/".$file;
                    $uploader_file=array('savepath'=>'','savename'=>$savename,'tmp_name'=>$tmpName);
                    $result=$upload->getUploader()->save($uploader_file);
                    if($result){
                        unlink($tmpName);
                        $return_img['state']='SUCCESS';
                        $return_img['url']=sp_get_image_preview_url($savename);
                        array_push( $list ,  $return_img );
                    }else{
                        $return_img['state']=$this->stateMap['ERROR_WRITE_CONTENT'];
                        array_push( $list , $return_img );
                    }

                }

                if(C('FILE_UPLOAD_TYPE')=='Local'){

                    $file = C("TMPL_PARSE_STRING.__UPLOAD__")."ueditor/$date/".$file;

                    $return_img['state']='SUCCESS';
                    $return_img['url']=$file;
                    array_push( $list ,  $return_img );
                }
            }else{
                $return_img['state']=$this->stateMap['ERROR_WRITE_CONTENT'];
                array_push( $list , $return_img );
            }
        }

        return json_encode(array(
            'state'=> count($list) ? 'SUCCESS':'ERROR',
            'list'=> $list
        ));
    }

    /**
     * 文件上传
     * @param array $config
     * @return string
     */
	private function ueditorUpload($filetype='image'){
        $uploadSetting = cmf_get_upload_setting();
        $fileImage     =  $this->request->file("upfile");
        $fileExtension = $fileImage->getExtension();//  cmf_get_file_extension($_FILES['upfile']['name']);
        $uploadMaxFileSize = $uploadSetting['upload_max_filesize'][$fileExtension];
        $uploadMaxFileSize = empty($uploadMaxFileSize)?2097152:$uploadMaxFileSize;//默认2M

        $adminid = cmf_get_current_admin_id();
        $userid  = cmf_get_current_userid();
        $userid  = empty($adminid)?$userid:$adminid;
        $strId   = $this->request->post("id");


        $allowedExts = explode(',', $uploadSetting[$filetype]);
        $strWebPath  = $this->request->root() . DS."up_files". DS."ueditor".DS;
        $strFilePath = ROOT_PATH . 'public' . DS."up_files". DS."ueditor".DS ;
        $arrResponse = array();


        if(!$fileImage->validate(['size'=>$uploadMaxFileSize,'ext'=>$allowedExts])->check()) {
            $arrReturn['state'] = $fileImage->getError();
            return json_encode($arrReturn);
        }

        $storageSetting=cmf_get_cmf_settings('storage');
        $qiniuSetting=$storageSetting['Qiniu']['setting'];
        $arrInfo = array();
        if(config('FILE_UPLOAD_TYPE')=='Qiniu' && $qiniuSetting['enable_picture_protect']){
            //todo  qianniu code ...

        }else{
            $info = $fileImage->move($strFilePath);//开始上传

            if(!$info)
            {
                $arrReturn['state'] = $fileImage->getError();
                return json_encode($arrReturn);
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
        $arrResponse["state"]      = 'SUCCESS';
        $arrResponse["url"]        =  $arrInfo["url"] ;
        $arrResponse["title"]      =  $arrInfo["filename"] ;
        $arrResponse["original"]   =  $arrInfo["filename"] ;

		
		return json_encode($arrResponse);
	}
    /**
     * 获取百度编辑器配置
     */
    private function ueditorConfig(){

        $config_text=preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("./static/js/ueditor/config.json"));
        $config = json_decode($config_text, true);
        $upload_setting=cmf_get_upload_setting();

        $config['imageMaxSize']=$upload_setting['image']['upload_max_filesize']*1024;
        $config['imageAllowFiles']= array_map(array($this,'ueditorExtension'), explode(",", $upload_setting['image']['extensions']));
        $config['scrawlMaxSize']=$upload_setting['image']['upload_max_filesize']*1024;
//
        $config['catcherMaxSize']=$upload_setting['image']['upload_max_filesize']*1024;
        $config['catcherAllowFiles']= array_map(array($this,'ueditorExtension'), explode(",", $upload_setting['image']['extensions']));

        $config['videoMaxSize']=$upload_setting['video']['upload_max_filesize']*1024;
        $config['videoAllowFiles']=   array_map(array($this,'ueditorExtension'), explode(",", $upload_setting['video']['extensions']));

        $config['fileMaxSize']=$upload_setting['file']['upload_max_filesize']*1024;
        $config['fileAllowFiles']= array_map(array($this,'ueditorExtension'), explode(",", $upload_setting['file']['extensions']));

        return json_encode($config);
    }

    /**
     * 格式化后缀
     * @param $str
     * @return string
     */
    private function ueditorExtension($str){

        return ".".trim($str,'.');
    }
}