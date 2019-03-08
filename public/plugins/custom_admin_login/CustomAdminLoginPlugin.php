<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\custom_admin_login;

use cmf\lib\Plugin;

class CustomAdminLoginPlugin extends Plugin
{
    public $info = [
        'name'        => 'CustomAdminLogin',
        'title'       => '自定义后台登录页demo',
        'description' => '自定义后台登录页demo',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0',
    ];

    public $hasAdmin = 0; //插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true; //安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true; //卸载成功返回true，失败false
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function adminLogin()
    {
        return $this->fetch('widget');
    }
}
