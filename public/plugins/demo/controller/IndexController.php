<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\demo\controller;

//Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use think\Db;

class IndexController extends PluginBaseController
{
    public function index()
    {
        $users = Db::name('user')->limit(0, 5)->select();
        $this->assign('users', $users);

        return $this->fetch('/index');
    }
}
