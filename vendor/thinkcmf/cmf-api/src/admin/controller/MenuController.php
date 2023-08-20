<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\service\AdminMenuService;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class MenuController extends RestAdminBaseController
{
    /**
     * 后台菜单列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/menus",
     *     summary="后台菜单列表",
     *     description="后台菜单列表,用于后台首页左侧菜单",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(ref="#/components/schemas/AdminMenuMenusResponse")
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function menus(AdminMenuService $adminMenuService)
    {
        $userId = $this->getUserId();
        $menus  = $adminMenuService->menus($userId);
        $this->success('success！', ['list' => $menus, 'total' => count($menus)]);
    }

}
