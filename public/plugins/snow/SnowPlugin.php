<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Powerless <wzxaini9@163.com>
// +----------------------------------------------------------------------
namespace plugins\snow;

use cmf\lib\Plugin;

class SnowPlugin extends Plugin
{

    public $info = [
        'name'=>'Snow',
        'title'=>'圣诞雪花',
        'description'=>'圣诞雪花特效',
        'status'=>1,
        'author'=>'ThinkCMF',
        'version'=>'1.0',
        'demo_url'    => 'http://demo.thinkcmf.com',
        'author_url'  => 'http://www.thinkcmf.com'
    ];

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

    //实现的footer钩子方法
    public function footerStart($param)
    {
        $config = $this->getConfig();
        $this->assign($config);
        echo $this->fetch('widget');
    }

}