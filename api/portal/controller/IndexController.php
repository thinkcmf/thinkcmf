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

namespace api\portal\controller;

use cmf\controller\RestBaseController;

class IndexController extends RestBaseController
{
    public function index()
    {
        $this->success("恭喜您,API访问成功!", [
            'version' => '6.0.0',
            'doc'     => 'http://www.thinkcmf.com/cmf5api.html'
        ]);
    }


}
