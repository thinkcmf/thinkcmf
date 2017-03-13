<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class PageController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch('/page');
    }
}
