<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Db;
use cmf\controller\AdminBaseController;

class ResourceController extends AdminBaseController
{
    public function index()
    {
        $result = Db::name('asset')->select();
        $this->assign('result',$result);
        $this->assign('status',['不可用','可用']);
        return $this->fetch();
    }
}