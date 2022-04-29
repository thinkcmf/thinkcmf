<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\portal\controller;

use cmf\controller\RestBaseController;

class IndexController extends RestBaseController
{
    /**
     * API首页
     * @OA\Get(
     *     tags={"home"},
     *     path="/",
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     */
    public function index()
    {
        $this->success("恭喜您,API访问成功!", [
            'version' => '1.1.0',
            'doc'     => 'http://www.thinkcmf.com/cmf5api.html'
        ]);
    }

}
