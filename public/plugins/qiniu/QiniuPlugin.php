<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\qiniu;

use cmf\lib\Plugin;
use Qiniu\Auth;


class QiniuPlugin extends Plugin
{

    public $info = [
        'name'        => 'Qiniu',
        'title'       => '七牛云存储',
        'description' => 'ThinkCMF七牛专享优惠码:507670e8',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0.1'
    ];

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        $storageOption = cmf_get_option('storage');
        if (empty($storageOption)) {
            $storageOption = [];
        }

        $storageOption['storages']['Qiniu'] = ['name' => '七牛云存储', 'driver' => '\\plugins\\qiniu\\lib\\Qiniu'];

        cmf_set_option('storage', $storageOption);
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        $storageOption = cmf_get_option('storage');
        if (empty($storageOption)) {
            $storageOption = [];
        }

        unset($storageOption['storages']['Qiniu']);

        cmf_set_option('storage', $storageOption);
        return true;//卸载成功返回true，失败false
    }

    public function fetchUploadView()
    {
        $tab = request()->param('tab');

        if ($tab == 'cloud') {
            $config     = $this->getConfig();
            $accessKey  = $config['accessKey'];
            $secretKey  = $config['secretKey'];
            $zone       = $config['zone'];
            $uploadHost = 'upload.qiniup.com';
            if (!empty($zone) && $zone != 'z0') {
                $uploadHost = "upload-{$zone}.qiniup.com";
            }
            $auth  = new Auth($accessKey, $secretKey);
            $token = $auth->uploadToken($config['bucket']);

            $this->assign('upload_host', $uploadHost);
            $this->assign('qiniu_up_token', $token);
            $content = $this->fetch('upload');
        } else {
            $content = "has_cloud_storage";
        }

        return $content;
    }

    public function cloudStorageTab(&$param)
    {



    }

}