<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use app\portal\model\NavMenuModel;

class IndexController extends HomeBaseController
{
    public function index()
    {
        $navMenuModel=new NavMenuModel();
        $navMenuModel->navMenusTreeArray(1);
        return $this->fetch(':index');
    }
}
