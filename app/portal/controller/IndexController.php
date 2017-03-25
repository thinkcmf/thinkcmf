<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch(':index');
    }
}
