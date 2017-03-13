<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class UserController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch(':index');
    }
}
