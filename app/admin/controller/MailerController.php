<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class MailerController extends AdminBaseController
{

    /**
     * 邮箱配置
     * @adminMenu(
     *     'name'   => '邮箱配置',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'order'  => 10000,
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

    // SMTP配置处理
    public function indexPost()
    {
        $post = array_map('trim', $this->request->param());

        if (in_array('', $post) && !empty($post['smtpsecure'])) {
            $this->error("不能留空！");
        }

        cmf_set_option('smtp_setting',$post);

        $this->success("保存成功！");
    }

    // 会员注册邮件模板
    public function active()
    {
        $template = cmf_get_option('email_template_user_activation');
        $this->assign($template);
        return $this->fetch();
    }

    // 会员注册邮件模板提交
    public function activePost()
    {
        $data=$this->request->param();

        // TODO 非空验证

        $data['template']=htmlspecialchars_decode($data['template']);

        cmf_set_option('email_template_user_activation',$data);

        $this->success("保存成功！");
    }

    // 邮件发送测试
    public function test()
    {
        if ($this->request->isPost()) {
            $rules = [
                ['to', 'require', '收件箱不能为空！', 1, 'regex', 3],
                ['to', 'email', '收件箱格式不正确！', 1, 'regex', 3],
                ['subject', 'require', '标题不能为空！', 1, 'regex', 3],
                ['content', 'require', '内容不能为空！', 1, 'regex', 3],
            ];

            $model = M(); // 实例化User对象
            if ($model->validate($rules)->create() !== false) {
                $data   = I('post.');
                $result = sp_send_email($data['to'], $data['subject'], $data['content']);
                if ($result && empty($result['error'])) {
                    $this->success('发送成功！');
                } else {
                    $this->error('发送失败：' . $result['message']);
                }
            } else {
                $this->error($model->getError());
            }

        } else {
            return $this->fetch();
        }

    }


}

