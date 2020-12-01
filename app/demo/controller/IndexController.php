<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\demo\controller;

use cmf\controller\HomeBaseController;
use think\facade\Db;

class IndexController extends HomeBaseController
{
    public function index()
    {
        Db::name('user')
            ->find();
       // return $this->fetch(':index');
    }

    public function test(){

    }

    public function ws()
    {
        return $this->fetch(':ws');
    }
}
