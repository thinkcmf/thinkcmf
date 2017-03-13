<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\demo\model;//Demo插件英文名，改成你的插件英文就行了
use think\Model;

//Demo插件英文名，改成你的插件英文就行了,插件数据表最好加个plugin前缀再加表名,这个类就是对应“表前缀+plugin_demo”表
class PluginDemoModel extends Model
{
    //自定义方法
    function test()
    {
        echo "hello";
    }
}