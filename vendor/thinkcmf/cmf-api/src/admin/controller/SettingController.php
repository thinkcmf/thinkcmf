<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use cmf\controller\RestAdminBaseController;
use cmf\controller\RestBaseController;
use OpenApi\Annotations as OA;
use think\facade\Db;
use think\facade\Validate;

class SettingController extends RestAdminBaseController
{
    /**
     * 清理缓存
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/setting/clearCache",
     *     summary="清理缓存",
     *     description="清理缓存",
     *     @OA\Response(
     *         response=200,
     *         description=""
     *     )
     * )
     */
    public function clearCache()
    {
        cmf_clear_cache();
        $this->success('清除成功！');
    }

}
