<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\swagger3;

//Demo插件英文名，改成你的插件英文就行了
use cmf\lib\Plugin;
use plugins\swagger3\service\OpenApi;

//Demo插件英文名，改成你的插件英文就行了
class Swagger3Plugin extends Plugin
{
    public $info = [
        'name'        => 'Swagger3', //Demo插件英文名，改成你的插件英文就行了
        'title'       => 'Swagger3',
        'description' => 'Swagger3.0支持PHP版本>=8.0',
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
        $api   = OpenApi::generate();
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
