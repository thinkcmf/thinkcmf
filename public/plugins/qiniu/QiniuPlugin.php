<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\qiniu;

use cmf\lib\Plugin;

class QiniuPlugin extends Plugin
{

    public $info = [
        'name'        => 'Qiniu',
        'title'       => '七牛云存储',
        'description' => '七牛云存储',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0'
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

}