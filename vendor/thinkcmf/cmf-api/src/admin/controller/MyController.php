<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\UserModel;
use app\admin\service\EmailService;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;
use think\Validate;

class MyController extends RestAdminBaseController
{
    /**
     * 当前管理员个人信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/my/info",
     *     summary="当前管理员个人信息",
     *     description="当前管理员个人信息",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "user":{
     *                  {"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function info()
    {
        $id   = $this->getUserId();
        $user = UserModel::where("id", $id)->find();

        $this->success('success', ['user' => $user]);
    }

    /**
     * 编辑当前管理员个人信息
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/my/info",
     *     summary="编辑当前管理员个人信息",
     *     description="编辑当前管理员个人信息",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMyInfoPutRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMyInfoPutRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function infoPut()
    {
        $data             = $this->request->param();
        $data['birthday'] = strtotime($data['birthday']);
        $userId           = $this->getUserId();
        /**
         * @var UserModel $user
         */
        $user = UserModel::find($userId);
        if (empty($user)) {
            $this->error('未找到用户！');
        }

        $user->allowField(['user_nickname', 'sex', 'birthday', 'user_url', 'signature'])->save($data);
        $this->success(lang('EDIT_SUCCESS'));
    }

    /**
     * 获取当前管理员我的邮箱设置
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/my/email/setting",
     *     summary="获取当前管理员我的邮箱设置",
     *     description="获取当前管理员我的邮箱设置",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "setting":{
     *                  {"from_name": "ThinkCMF","from": "no-reply@thinkcmf.com",
     *                  "host": "smtp.thinkcmf.com","smtp_secure": "ssl",
     *                  "port": "463","username": "463","password": "发水电费",
     *                  "signature": "AD书法大师"}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function emailSetting()
    {
        $adminId      = $this->getUserId();
        $emailSetting = cmf_get_option('admin_smtp_setting_' . $adminId);

        $this->success('success', ['setting' => $emailSetting]);
    }

    /**
     * 当前管理员我的邮箱设置提交保存
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/my/email/setting",
     *     summary="当前管理员我的邮箱设置提交保存",
     *     description="当前管理员我的邮箱设置提交保存",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMyEmailSettingPutRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMyEmailSettingPutRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function emailSettingPut()
    {
        if ($this->request->isPut()) {
            $post = array_map('trim', $this->request->param());

            if (in_array('', $post) && !empty($post['smtpsecure'])) {
                $this->error("不能留空！");
            }

            $adminId = cmf_get_current_admin_id();
            cmf_set_option('admin_smtp_setting_' . $adminId, $post);

            $this->success(lang('EDIT_SUCCESS'));
        }
    }

    /**
     * 当前管理员我的邮箱设置测试
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/my/email/setting/test",
     *     summary="当前管理员我的邮箱设置测试",
     *     description="当前管理员我的邮箱设置测试",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMailTestRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMailTestRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function emailSettingTest()
    {
        if ($this->request->isPost()) {

            $validate = new Validate();
            $validate->rule([
                'to'      => 'require|email',
                'subject' => 'require',
                'content' => 'require',
            ]);
            $validate->message([
                'to.require'      => '收件箱不能为空！',
                'to.email'        => '收件箱格式不正确！',
                'subject.require' => '标题不能为空！',
                'content.require' => '内容不能为空！',
            ]);

            $data = $this->request->param();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $result = EmailService::send($data['to'], $data['subject'], $data['content']);
            if ($result && empty($result['error'])) {
                $this->success('发送成功！');
            } else {
                $this->error('发送失败：' . $result['message']);
            }

        } else {
            return $this->fetch();
        }
    }


}
