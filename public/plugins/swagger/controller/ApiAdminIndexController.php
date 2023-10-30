<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\swagger\controller;

//Demo插件英文名，改成你的插件英文就行了


use cmf\controller\PluginRestAdminBaseController;
use plugins\swagger\service\OpenApi;

/**
 * Class AdminIndexController.
 */
class ApiAdminIndexController extends PluginRestAdminBaseController
{


    /**
     * 导入后台API接口
     * Throws \think\exception\DbException
     * @OA\Post(
     *     tags={"plugin/Swagger"},
     *     path="/plugin/swagger/api_admin_index/importApi",
     *     summary="导入后台API接口",
     *     description="导入后台API接口",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"success": "success","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function importApi()
    {
        $api   = OpenApi::generate();
        $api   = json_decode($api->toJson(), true);
        $paths = $api['paths'];

        foreach ($paths as $path => $methods) {
            $path = trim(preg_replace("/\{([0-9a-zA-Z_]+)\}/", ':$1', $path), '/');
            if (!(str_starts_with($path, "admin") || str_contains($path, "/admin") || str_contains($path, '/api_admin_'))) {
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

                    $ruleName = strtolower("admin_api:$url");

                    $findAuthRule = db('auth_rule')->where('name', $ruleName)->find();
                    if ($findAuthRule) {
                        db('auth_rule')->where('id', $findAuthRule['id'])->update([
                            'app'   => $methodData['tags'][0],
                            'type'  => 'admin_api',
                            'name'  => $ruleName,
                            'title' => empty($methodData['summary']) ? '' : $methodData['summary'],
                        ]);
                    } else {
                        db('auth_rule')->insert([
                            'app'   => $methodData['tags'][0],
                            'type'  => 'admin_api',
                            'name'  => $ruleName,
                            'title' => empty($methodData['summary']) ? '' : $methodData['summary'],
                        ]);
                    }
                }

            }
        }
        $this->success("API导入成功！");
    }


}
