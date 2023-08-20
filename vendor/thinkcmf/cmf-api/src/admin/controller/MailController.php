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
use OpenApi\Annotations as OA;
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

    /**
     *  测试系统邮箱配置
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/mail/test",
     *     summary="测试系统邮箱配置",
     *     description="测试系统邮箱配置",
     *     @OA\RequestBody(
     *         required=true,
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
     *          @OA\JsonContent(example={"code": 1,"msg": "发送成功！","data": ""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "发送失败！","data": ""})
     *     ),
     * )
     */
    public function test()
    {
        if ($this->request->isPost()) {

            $validate = new \think\Validate();
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

            $result = cmf_send_email($data['to'], $data['subject'], $data['content']);
            if ($result && empty($result['error'])) {
                $this->success('发送成功！');
            } else {
                $this->error('发送失败：' . $result['message']);
            }

        }
    }

    /**
     * 获取邮箱数字验证码模板配置
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/mail/template",
     *     summary="获取邮箱数字验证码模板配置",
     *     description="获取邮箱数字验证码模板配置",
     *     @OA\Parameter(
     *         name="template_key",
     *         in="query",
     *         description="模板类型键值",
     *         example="verification_code",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "success",
     *             "data": {
     *                  "config": {
     *                      "subjuct": "ThinkCMF数字验证码",
     *                      "template": "<p>您的数字验证码是{$code}。</p>"
     *                  }
     *              }
     *          })
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error","data": ""})
     *     )
     * )
     */
    public function template()
    {
        $allowedTemplateKeys = ['verification_code'];
        $templateKey         = $this->request->param('template_key');

        if (empty($templateKey) || !in_array($templateKey, $allowedTemplateKeys)) {
            $this->error(lang('illegal request'));
        }

        $template = cmf_get_option('email_template_' . $templateKey);
        if (isset($template['template'])) {
            $template['template'] = htmlspecialchars_decode($template['template']);
        }
        $this->success("success", [
            'config' => $template,
        ]);
    }

    /**
     * 更新邮箱数字验证码模板
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/mail/template",
     *     summary="更新邮箱数字验证码模板",
     *     description="更新邮箱数字验证码模板",
     *     @OA\RequestBody(
     *         required=true,
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMailTemplatePutRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMailTemplatePutRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功！","data": ""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "保存失败！","data": ""})
     *     ),
     * )
     */
    public function templatePut()
    {
        if ($this->request->isPut()) {
            $allowedTemplateKeys = ['verification_code'];
            $templateKey         = $this->request->param('template_key');

            if (empty($templateKey) || !in_array($templateKey, $allowedTemplateKeys)) {
                $this->error(lang('illegal request'));
            }

            $data = $this->request->param();

            unset($data['template_key']);

            cmf_set_option('email_template_' . $templateKey, $data);

            $this->success(lang('EDIT_SUCCESS'));
        }
    }

}
