<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\App;
use think\Loader;

class PluginController extends HomeBaseController
{
    public function index($_plugin, $_controller, $_action)
    {

        $_controller = Loader::parseName($_controller, 1);

        $pluginControllerClass = "plugins\\{$_plugin}\\controller\\{$_controller}Controller";;

        $vars = [];
        return App::invokeMethod([$pluginControllerClass, $_action, $vars]);
    }

}
