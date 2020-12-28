<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace cmf\controller;

use think\captcha\Captcha;
use think\facade\Config;
use think\Request;

class SwaggerController
{
    /**
     */
    public function index()
    {
        return view(__DIR__ . '/../tpl/swagger.tpl');
    }

    public function config()
    {
//        $openapi = \OpenApi\scan(CMF_ROOT . 'vendor/zircote/swagger-php/Examples/petstore-3.0');
//
//        echo $openapi->toJson();exit;
//        $openapi = \OpenApi\scan(CMF_ROOT . 'vendor/zircote/swagger-php/Examples/petstore.swagger.io');
        header('Content-Type: application/json');
        $api    = \OpenApi\scan([
            CMF_ROOT . 'api',
            CMF_ROOT . 'vendor/thinkcmf/cmf-api'
        ]);

        echo $api->toJson();
        exit;
    }
}
