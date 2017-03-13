<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class SearchController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch('/search');
    }
}
