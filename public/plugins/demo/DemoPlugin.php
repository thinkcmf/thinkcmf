<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\demo;//Demo插件英文名，改成你的插件英文就行了
use cmf\lib\Plugin;

//Demo插件英文名，改成你的插件英文就行了
class DemoPlugin extends Plugin
{

    public $info = array(
        'name'        => 'Demo',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '插件演示',
        'description' => '插件演示',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0'
    );

    public $hasAdmin = 1;//插件是否有后台管理界面

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

    //实现的footer_start钩子方法
    public function footerStart($param)
    {
        $config = $this->getConfig();
        $this->assign($config);
        $this->fetch('widget');
    }

}