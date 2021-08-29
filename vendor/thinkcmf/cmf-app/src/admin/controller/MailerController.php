<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Validate;

class MailerController extends AdminBaseController
{

    /**
     * 邮箱配置
     * @adminMenu(
     *     'name'   => '邮箱配置',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '邮箱配置',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $emailSetting = cmf_get_option('smtp_setting');
        $this->assign($emailSetting);
        return $this->fetch();
    }

    /**
     * 邮箱配置
     * @adminMenu(
     *     'name'   => '邮箱配置提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮箱配置提交保存',
     *     'param'  => ''
     * )
     */
    public function indexPost()
    {
        if ($this->request->isPost()) {
            $post = array_map('trim', $this->request->param());

            if (in_array('', $post) && !empty($post['smtpsecure'])) {
                $this->error(lang('ADMIN_SMTP_EMPTY'));
            }

            cmf_set_option('smtp_setting', $post);

            $this->success(lang('EDIT_SUCCESS'));
        }
    }

    /**
     * 邮件模板
     * @adminMenu(
     *     'name'   => '邮件模板',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮件模板',
     *     'param'  => ''
     * )
     */
    public function template()
    {
        $allowedTemplateKeys = ['verification_code'];
        $templateKey = $this->request->param('template_key');

        if (empty($templateKey) || !in_array($templateKey, $allowedTemplateKeys)) {
            $this->error(lang('ILLEGAL_REQUEST'));
        }

        $template = cmf_get_option('email_template_' . $templateKey);
        $this->assign($template);
        return $this->fetch('template_verification_code');
    }

    /**
     * 邮件模板提交
     * @adminMenu(
     *     'name'   => '邮件模板提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮件模板提交',
     *     'param'  => ''
     * )
     */
    public function templatePost()
    {
        if ($this->request->isPost()) {
            $allowedTemplateKeys = ['verification_code'];
            $templateKey = $this->request->param('template_key');

            if (empty($templateKey) || !in_array($templateKey, $allowedTemplateKeys)) {
                $this->error(lang('ILLEGAL_REQUEST'));
            }

            $data = $this->request->param();

            unset($data['template_key']);

            cmf_set_option('email_template_' . $templateKey, $data);

            $this->success(lang('EDIT_SUCCESS'));
        }
    }

    /**
     * 邮件发送测试
     * @adminMenu(
     *     'name'   => '邮件发送测试',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮件发送测试',
     *     'param'  => ''
     * )
     */
    public function test()
    {
        if ($this->request->isPost()) {

            $validate = new Validate();
            $validate->rule([
                'to'      => 'require|email',
                'subject' => 'require',
                'content' => 'require',
            ]);
            $validate->message([
                'to.require'      => lang('ADMIN_SENDER_EMPTY'),
                'to.email'        => lang('ADMIN_SENDER_FORMAT'),
                'subject.require' => lang('ADMIN_SENDER_SUBJECT'),
                'content.require' => lang('ADMIN_SENDER_CONTENT'),
            ]);

            $data = $this->request->param();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $result = cmf_send_email($data['to'], $data['subject'], $data['content']);
            if ($result && empty($result['error'])) {
                $this->success(lang('ADMIN_SEND_SUCCESS'));
            } else {
                $this->error(lang('ADMIN_SEND_FAIL') . ': ' . $result['message']);
            }

        } else {
            return $this->fetch();
        }

    }


}

