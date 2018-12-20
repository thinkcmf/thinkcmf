<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: catman <catman@thinkcmf.com>
// +---------------------------------------------------------------------
// ThinkPHP5.0兼容ThinkPHP5.1代码,用法请看ThinkPHP5.1文档
namespace think\facade;

use think\Config as ThinkConfig;

class Config
{
    /**
     * 获取一级配置
     * @access public
     * @param  string    $name 一级配置名
     * @return array
     */
    public static function pull($name)
    {
       return ThinkConfig::get($name);
    }
}