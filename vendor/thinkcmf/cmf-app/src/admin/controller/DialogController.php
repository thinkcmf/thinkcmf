<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class DialogController extends AdminBaseController
{
    public function initialize()
    {

    }

    public function map()
    {
        $location = $this->request->param('location');
        $location = explode(',', $location);
        $lng      = empty($location[0]) ? 116.424966 : $location[0];
        $lat      = empty($location[1]) ? 39.907851 : $location[1];

        $this->assign(['lng' => $lng, 'lat' => $lat]);
        return $this->fetch();
    }

}