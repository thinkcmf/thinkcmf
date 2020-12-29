<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace cmf\controller;

use think\captcha\Captcha;
use think\exception\HttpResponseException;
use think\facade\Config;
use think\Request;
use think\Response;

class SwaggerController
{
    public function index()
    {
        return view(__DIR__ . '/../tpl/swagger.tpl');
    }

    public function config()
    {
        header('Content-Type: application/json');
        $api    = \OpenApi\scan([
            CMF_ROOT . 'api',
            CMF_ROOT . 'vendor/thinkcmf/cmf-api'
        ]);

        $response = Response::create(json_decode($api->toJson(),true),'json');
        throw new HttpResponseException($response);
    }
}
