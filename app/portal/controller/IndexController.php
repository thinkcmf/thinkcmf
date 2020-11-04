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

namespace app\portal\controller;

use app\portal\model\UserModel;
use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{
    public function index()
    {

        return $this->fetch(':index');
    }

    public function hello()
    {
        echo url('index/Index/hello');
        return 'hello2';
    }


}
