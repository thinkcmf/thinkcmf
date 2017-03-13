<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\demo\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\Demo\Model\PluginDemoModel;
use think\Db;

class IndexController extends PluginBaseController
{

    function index($id)
    {

        $users = Db::name("user")->limit(0, 5)->select();
        $demos=PluginDemoModel::all();

       // print_r($demos);

        $this->assign("users", $users);

        return $this->fetch("/index");
    }

}
