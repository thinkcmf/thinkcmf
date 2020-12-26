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
        $api    = \OpenApi\scan(CMF_ROOT . 'api');
        $apiArr = json_decode($api->toJson(), true);
        //$openapi = \OpenApi\scan(CMF_ROOT . 'vendor/zircote/swagger-php/Examples/petstore-3.0');

//        $openapi = \OpenApi\scan(CMF_ROOT . 'vendor/zircote/swagger-php/Examples/petstore.swagger.io');
        header('Content-Type: application/json');
        $cmfApi    = \OpenApi\scan(CMF_ROOT . 'vendor/thinkcmf/cmf-api');
        $cmfApiArr = json_decode($cmfApi->toJson(), true);
        $apiArr    = array_replace_recursive($cmfApiArr, $apiArr);

        echo json_encode($apiArr);
        exit;
    }
}
