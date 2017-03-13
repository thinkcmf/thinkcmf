<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\LinkModel;

class LinkController extends AdminBaseController
{
    // 友情链接列表
    public function index()
    {
        $linkModel = new LinkModel();
        $links     = $linkModel->select();
        $this->assign('links', $links);

        return $this->fetch();
    }
}