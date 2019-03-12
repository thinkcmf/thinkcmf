<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\switch_admin_theme_demo;//Demo插件英文名，改成你的插件英文就行了
use cmf\lib\Plugin;

//Demo插件英文名，改成你的插件英文就行了
class SwitchAdminThemeDemoPlugin extends Plugin
{

    public $info = [
        'name'        => 'SwitchAdminThemeDemo',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '后台模板切换演示',
        'description' => '后台模板切换演示',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0.0',
        'demo_url'    => 'http://demo.thinkcmf.com',
        'author_url'  => 'http://www.thinkcmf.com'
    ];

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    //实现的switch_admin_theme钩子方法
    public function switchAdminTheme($param)
    {
        $config = $this->getConfig();

        $mobileTheme = empty($config['mobile_theme']) ? '' : $config['mobile_theme'];

        return $mobileTheme;
    }

}