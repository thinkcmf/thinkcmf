<?php

namespace cmf\service;

use think\Route;
use think\Service;
use think\Validate;

class CaptchaService extends Service
{
    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            $route->get('new_captcha', "\\cmf\\controller\\CaptchaController@test");
        });
    }
}
