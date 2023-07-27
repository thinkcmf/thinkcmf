<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\swagger;

//Demo插件英文名，改成你的插件英文就行了
use cmf\lib\Plugin;

//Demo插件英文名，改成你的插件英文就行了
class SwaggerPlugin extends Plugin
{
    public $info = [
        'name'        => 'Swagger', //Demo插件英文名，改成你的插件英文就行了
        'title'       => 'Swagger',
        'description' => 'Swagger',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0.1',
        'demo_url'    => 'http://demo.thinkcmf.com',
        'author_url'  => 'http://www.thinkcmf.com',
    ];

    public $hasAdmin = 1; //插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true; //安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true; //卸载成功返回true，失败false
    }

    public function adminApiImportView()
    {
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
        $api   = \OpenApi\Generator::scan($paths,
            [
                'aliases'  => [
                    'oa' => 'OpenApi\\Annotations'
                ],
//                    'namespaces' => ['OpenApi\\Annotations\\'],
                'validate' => false
            ]
        );
        $api   = json_decode($api->toJson(), true);
        $paths = $api['paths'];

        foreach ($paths as $path => $methods) {
            $path = trim(preg_replace("/\{(.+)\}/", ':$1', $path), '/');
            if (!str_starts_with($path, "admin")) {
                continue;
            }
            if (!empty($path)) {
                foreach ($methods as $method => $methodData) {
                    $url          = strtoupper($method) . '|' . $path;
                    $findAdminApi = db('admin_api')->where('url', $url)->find();
                    if (empty($findAdminApi)) {
                        db('admin_api')->insert([
                            'parent_id' => 0,
                            'type'      => 1,
                            'url'       => $url,
                            'name'      => empty($methodData['summary']) ? '' : $methodData['summary'],
                            'remark'    => empty($methodData['description']) ? '' : $methodData['description'],
                            'tags'      => join(',', $methodData['tags'])
                        ]);
                    } else {
                        db('admin_api')->where('id', $findAdminApi['id'])->update([
                            'parent_id' => 0,
                            'type'      => 1,
                            'url'       => $url,
                            'name'      => empty($methodData['summary']) ? '' : $methodData['summary'],
                            'remark'    => empty($methodData['description']) ? '' : $methodData['description'],
                            'tags'      => join(',', $methodData['tags'])
                        ]);
                    }
                }

            }
        }

        return $this->fetch('widget');
    }

}
