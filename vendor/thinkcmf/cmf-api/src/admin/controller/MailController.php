<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\RestAdminBaseController;
use cmf\controller\RestBaseController;
use OpenApi\Annotations as OA;
use think\facade\Db;
use think\facade\Validate;

/**
 *
 */
class MailController extends RestAdminBaseController
{
    /**
     * 获取系统邮箱配置
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/mail/config",
     *     summary="获取系统邮箱配置",
     *     description="获取系统邮箱配置",
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "success",
     *             "data": {
     *                  "config": {
     *                      "from_name": "fasd",
     *                      "from": "sfdss@s.dom",
     *                      "host": "sss",
     *                      "smtp_secure": "",
     *                      "port": "asd",
     *                      "username": "fsad",
     *                      "password": "fasd"
     *                  }
     *              }
     *          })
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error","data": ""})
     *     ),
     * )
     */
    public function config()
    {
        $emailSetting = cmf_get_option('smtp_setting');
        $this->success("success", [
            'config' => $emailSetting,
        ]);
    }

    /**
     * 更新系统邮箱配置
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/mail/config",
     *     summary="更新系统邮箱配置",
     *     description="更新系统邮箱配置",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMailConfigPutRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMailConfigPutRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功!","data": ""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "保存失败！","data": ""})
     *     ),
     * )
     */
    public function configPut()
    {
        $post = array_map('trim', $this->request->param());

        if (in_array('', $post) && !empty($post['smtpsecure'])) {
            $this->error("不能留空！");
        }

        cmf_set_option('smtp_setting', $post);
        $this->success(lang('EDIT_SUCCESS'));
    }

}
