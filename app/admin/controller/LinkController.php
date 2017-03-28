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
    /**
     * 友情链接列表
     * @adminMenu(
     *     'name'=>'友情链接管理',
     *     'parent'=>'admin/Setting/default',
     *     'display'=>true,
     *     'order'=>10000,
     *     'icon'=>'',
     *     'remark'=>'友情链接列表',
     *     'param'=>''
     * )
     */
    public function index()
    {
        $linkModel = new LinkModel();
        $links     = $linkModel->select();
        $this->assign('links', $links);

        return $this->fetch();
    }

    /**
     *  添加友情链接
     * @adminMenu(
     *     'name'=>'添加友情链接',
     *     'parent'=>'index',
     *     'display'=>false,
     *     'order'=>10000,
     *     'icon'=>'',
     *     'remark'=>'添加友情链接',
     *     'param'=>''
     * )
     */
    public function add()
    {

    }

}