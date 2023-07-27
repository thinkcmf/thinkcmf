<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\swagger\controller;

//Demo插件英文名，改成你的插件英文就行了
use api\ApiInfo;
use cmf\controller\PluginBaseController;
use think\exception\HttpResponseException;
use think\Response;

class IndexController extends PluginBaseController
{
    public function index()
    {
        if (APP_DEBUG || cmf_get_current_admin_id() > 0) {
            return $this->fetch('/swagger');
        } else {
            return "请打开开发者模式，或者登录后台";
        }
    }

    public function config()
    {
        if (APP_DEBUG || cmf_get_current_admin_id() > 0) {
            header('Content-Type: application/json');
            $paths = [WEB_ROOT . 'plugins/swagger/swagger', CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/swagger'];
            $apps  = cmf_scan_dir(CMF_ROOT . 'api/*', GLOB_ONLYDIR);

            foreach ($apps as $app) {
                $dir = CMF_ROOT . 'api/' . $app . '/controller';
                if (is_dir($dir)) {
                    $paths[] = $dir;
                }

                $dir = CMF_ROOT . 'api/' . $app . '/swagger';
                if (is_dir($dir)) {
                    $paths[] = $dir;
                }
            }

            $apps = cmf_scan_dir(CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/*', GLOB_ONLYDIR);
            foreach ($apps as $app) {
                $dir = CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/' . $app . '/controller';
                if (is_dir($dir)) {
                    $paths[] = $dir;
                }

                $dir = CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/' . $app . '/swagger';
                if (is_dir($dir)) {
                    $paths[] = $dir;
                }
            }
            $api = \OpenApi\Generator::scan($paths,
                [
                    'aliases'  => [
                        'oa' => 'OpenApi\\Annotations'
                    ],
//                    'namespaces' => ['OpenApi\\Annotations\\'],
                    'validate' => false
                ]
            );

            $response = Response::create(json_decode($api->toJson(), true), 'json');
            throw new HttpResponseException($response);
        }
    }
}
