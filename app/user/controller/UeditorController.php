<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: kane <chengjin005@163.com>
// +----------------------------------------------------------------------

namespace app\user\controller;

use cmf\controller\HomeBaseController;
use cmf\lib\Upload;
use think\exception\HttpResponseException;
use think\Response;

/**
 * 百度编辑器文件上传处理控制器
 * Class Ueditor.
 */
class UeditorController extends HomeBaseController
{
    private $stateMap = [ //上传状态映射表，国际化用户需考虑此处数据的国际化
        'SUCCESS', //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        '文件大小超出 upload_max_filesize 限制',
        '文件大小超出 MAX_FILE_SIZE 限制',
        '文件未被完整上传',
        '没有文件被上传',
        '上传文件为空',
        'ERROR_TMP_FILE'           => '临时文件错误',
        'ERROR_TMP_FILE_NOT_FOUND' => '找不到临时文件',
        'ERROR_SIZE_EXCEED'        => '文件大小超出网站限制',
        'ERROR_TYPE_NOT_ALLOWED'   => '文件类型不允许',
        'ERROR_CREATE_DIR'         => '目录创建失败',
        'ERROR_DIR_NOT_WRITEABLE'  => '目录没有写权限',
        'ERROR_FILE_MOVE'          => '文件保存时出错',
        'ERROR_FILE_NOT_FOUND'     => '找不到上传文件',
        'ERROR_WRITE_CONTENT'      => '写入文件内容错误',
        'ERROR_UNKNOWN'            => '未知错误',
        'ERROR_DEAD_LINK'          => '链接不可用',
        'ERROR_HTTP_LINK'          => '链接不是http链接',
        'ERROR_HTTP_CONTENTTYPE'   => '链接contentType不正确',
    ];

    /**
     * 初始化.
     */
    public function initialize()
    {
        $adminId = cmf_get_current_admin_id();
        $userId = cmf_get_current_user_id();
        if (empty($adminId) && empty($userId)) {
            $this->error('非法上传！');
        }
    }

    /**
     * 处理上传处理.
     */
    public function upload()
    {
//        error_reporting(E_ERROR);
//        header("Content-Type: text/html; charset=utf-8");

        $action = $this->request->param('action');

        switch ($action) {

            case 'config':
                $result = $this->ueditorConfig();
                break;

            /* 上传图片 */
            case 'uploadimage':
                $result = $this->ueditorUpload('image');
                break;
            /* 上传涂鸦 */
            case 'uploadscrawl':
                $result = $this->ueditorUpload('image');
                break;
            /* 上传视频 */
            case 'uploadvideo':
                $result = $this->ueditorUpload('video');
                break;
            /* 上传文件 */
            case 'uploadfile':
                $result = $this->ueditorUpload('file');
                break;

            /* 列出图片 */
            case 'listimage':
                $result = '';
                break;
            /* 列出文件 */
            case 'listfile':
                $result = '';
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
        if (isset($_GET['callback']) && false) {//TODO 跨域上传
            if (preg_match("/^[\w_]+$/", $_GET['callback'])) {
                echo htmlspecialchars($_GET['callback']).'('.$result.')';
            } else {
                echo json_encode([
                    'state' => 'callback参数不合法',
                ]);
            }
        } else {
            $response = Response::create(json_decode($result, true), 'json');

            throw new HttpResponseException($response);
        }
    }

    /**
     * 获取远程图片.
     */
    private function _get_remote_image()
    {
        $source = $this->request->param('source');

        $item = [
            'state'    => '',
            'url'      => '',
            'size'     => '',
            'title'    => '',
            'original' => '',
            'source'   => '',
        ];
        $date = date('Ymd');
        $uploadSetting = cmf_get_upload_setting();
        $uploadMaxFileSize = $uploadSetting['image']['upload_max_filesize'];
        $uploadMaxFileSize = empty($uploadMaxFileSize) ? 2048 : $uploadMaxFileSize; //默认2M
        $allowedExts = explode(',', $uploadSetting['image']['extensions']);
        $strSavePath = ROOT_PATH.'public'.DIRECTORY_SEPARATOR.'ueditor'.DIRECTORY_SEPARATOR.$date.DIRECTORY_SEPARATOR;
        //远程抓取图片配置
        $config = [
            'savePath'   => $strSavePath,            //保存路径
            'allowFiles' => $allowedExts, // [".gif", ".png", ".jpg", ".jpeg", ".bmp"], //文件允许格式
            'maxSize'    => $uploadMaxFileSize,                    //文件大小限制，单位KB
        ];

        $storage_setting = cmf_get_cmf_settings('storage');
        $qiniu_domain = $storage_setting['Qiniu']['domain'];
        $no_need_domains = [$qiniu_domain];

        $list = [];
        foreach ($source as $imgUrl) {
            $host = str_replace(['http://', 'https://'], '', $imgUrl);
            $host = explode('/', $host);
            $host = $host[0];
            if (in_array($host, $no_need_domains)) {
                continue;
            }
            $return_img = $item;
            $return_img['source'] = $imgUrl;
            $imgUrl = htmlspecialchars($imgUrl);
            $imgUrl = str_replace('&amp;', '&', $imgUrl);
            //http开头验证
            if (strpos($imgUrl, 'http') !== 0) {
                $return_img['state'] = $this->stateMap['ERROR_HTTP_LINK'];
                array_push($list, $return_img);
                continue;
            }

            //获取请求头
            // is_sae()

            if (!cmf_is_sae()) {//SAE下无效
                $heads = get_headers($imgUrl);
                //死链检测
                if (!(stristr($heads[0], '200') && stristr($heads[0], 'OK'))) {
                    $return_img['state'] = $this->stateMap['ERROR_DEAD_LINK'];
                    array_push($list, $return_img);
                    continue;
                }
            }

            //格式验证(扩展名验证和Content-Type验证)
            $fileType = strtolower(strrchr($imgUrl, '.'));
            if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], 'image')) {
                $return_img['state'] = $this->stateMap['ERROR_HTTP_CONTENTTYPE'];
                array_push($list, $return_img);
                continue;
            }

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                [
                    'http' => [
                        'follow_location' => false, // don't follow redirects
                    ],
                ]
            );
            //请确保php.ini中的fopen wrappers已经激活
            readfile($imgUrl, false, $context);
            $img = ob_get_contents();
            ob_end_clean();

            //大小验证
            $uriSize = strlen($img); //得到图片大小
            $allowSize = 1024 * $config['maxSize'];
            if ($uriSize > $allowSize) {
                $return_img['state'] = $this->stateMap['ERROR_SIZE_EXCEED'];
                array_push($list, $return_img);
                continue;
            }

            $file = uniqid().strrchr($imgUrl, '.');
            $savePath = $config['savePath'];
            $tmpName = $savePath.$file;

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
                    $file = $strSavePath.$file;

                    $return_img['state'] = 'SUCCESS';
                    $return_img['url'] = $file;
                    array_push($list, $return_img);
                }
            } else {
                $return_img['state'] = $this->stateMap['ERROR_WRITE_CONTENT'];
                array_push($list, $return_img);
            }
        }

        return json_encode([
            'state' => count($list) ? 'SUCCESS' : 'ERROR',
            'list'  => $list,
        ]);
    }

    /**
     * 文件上传.
     *
     * @param string $fileType 文件类型
     *
     * @return string
     */
    private function ueditorUpload($fileType = 'image')
    {
        $uploader = new Upload();
        $uploader->setFileType($fileType);
        $uploader->setFormName('upfile');
        $result = $uploader->upload();

        if ($result === false) {
            return json_encode([
                'state' => $uploader->getError(),
            ]);
        } else {
            return json_encode([
                'state'    => 'SUCCESS',
                'url'      => $result['url'],
                'title'    => $result['name'],
                'original' => $result['name'],
            ]);
        }
    }

    /**
     * 获取百度编辑器配置.
     */
    private function ueditorConfig()
    {
        $config_text = preg_replace("/\/\*[\s\S]+?\*\//", '', file_get_contents(WEB_ROOT.'static/js/ueditor/config.json'));
        $config = json_decode($config_text, true);
        $upload_setting = cmf_get_upload_setting();

        $config['imageMaxSize'] = $upload_setting['file_types']['image']['upload_max_filesize'] * 1024;
        $config['imageAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(',', $upload_setting['file_types']['image']['extensions']));
        $config['scrawlMaxSize'] = $upload_setting['file_types']['image']['upload_max_filesize'] * 1024;
//
        $config['catcherMaxSize'] = $upload_setting['file_types']['image']['upload_max_filesize'] * 1024;
        $config['catcherAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(',', $upload_setting['file_types']['image']['extensions']));

        $config['videoMaxSize'] = $upload_setting['file_types']['video']['upload_max_filesize'] * 1024;
        $config['videoAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(',', $upload_setting['file_types']['video']['extensions']));

        $config['fileMaxSize'] = $upload_setting['file_types']['file']['upload_max_filesize'] * 1024;
        $config['fileAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(',', $upload_setting['file_types']['file']['extensions']));

        return json_encode($config);
    }

    /**
     * 格式化后缀
     *
     * @param $str
     *
     * @return string
     */
    private function ueditorExtension($str)
    {
        return '.'.trim($str, '.');
    }
}
