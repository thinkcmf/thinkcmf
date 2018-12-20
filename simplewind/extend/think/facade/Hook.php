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

use think\Hook as ThinkHook;

class Hook
{
    /**
     * 监听标签的行为
     * @access public
     * @param  string $tag    标签名称
     * @param  mixed  $params 传入参数
     * @param  bool   $once   只获取一个有效返回值
     * @return mixed
     */
    public static function listen($tag, $params = null, $once = false)
    {
        return ThinkHook::listen($tag, $params, null, $once);
    }

    /**
     * 动态添加行为扩展到某个标签
     * @access public
     * @param  string $tag      标签名称
     * @param  mixed  $behavior 行为名称
     * @param  bool   $first    是否放到开头执行
     * @return void
     */
    public static function add($tag, $behavior, $first = false)
    {
        ThinkHook::add($tag, $behavior, $first);
    }

}