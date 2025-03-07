<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\service;

use app\admin\model\LinkModel;
use app\admin\model\SlideItemModel;
use app\admin\model\SlideModel;

class EmailService
{
    public static function send($address, $subject, $message, $attachments = [], $params = [])
    {
        $adminId = cmf_get_current_admin_id();
        if (is_int($params)) { //兼容老用法
            $adminId = $params;
        } elseif (!empty($params['admin_id'])) {
            $adminId = $params['admin_id'];
        }

        $smtpSetting = cmf_get_option('admin_smtp_setting_' . $adminId);
        if (empty($smtpSetting)) {
            return ["error" => 1, "message" => '没有邮箱配置！'];
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        // 设置PHPMailer使用SMTP服务器发送Email
        $mail->IsSMTP();
        $mail->IsHTML(true);
        //$mail->SMTPDebug = 3;
        // 设置邮件的字符编码，若不指定，则为'UTF-8'
        $mail->CharSet = 'UTF-8';
        // 添加收件人地址，可以多次使用来添加多个收件人
        $mail->AddAddress($address);
        if (!empty($params['CCs'])) {
            foreach ($params['CCs'] as $CC) {
                if (is_string($CC)) {
                    $mail->addCC($CC);
                }
            }
        }
        // 设置邮件正文
        $mail->Body = $message . (empty($smtpSetting['signature']) ? '' : htmlspecialchars_decode($smtpSetting['signature']));
        // 设置邮件头的From字段。
        $mail->From = $smtpSetting['from'];
        // 设置发件人名字
        $mail->FromName = $smtpSetting['from_name'];
        // 设置邮件标题
        $mail->Subject = $subject;

        if (!empty($attachments)) {
            foreach ($attachments as $name => $attachment) {
                $mail->addAttachment($attachment, $name);
            }
        }

        // 设置SMTP服务器。
        $mail->Host = $smtpSetting['host'];
        //by Rainfer
        // 设置SMTPSecure。
        $Secure           = $smtpSetting['smtp_secure'];
        $mail->SMTPSecure = empty($Secure) ? '' : $Secure;
        // 设置SMTP服务器端口。
        $port       = $smtpSetting['port'];
        $mail->Port = empty($port) ? "25" : $port;
        // 设置为"需要验证"
        $mail->SMTPAuth    = true;
        $mail->SMTPAutoTLS = false;
        $mail->Timeout     = 10;
        // 设置用户名和密码。
        $mail->Username = $smtpSetting['username'];
        $mail->Password = $smtpSetting['password'];
        // 发送邮件。
        if (!$mail->Send()) {
            $mailError = $mail->ErrorInfo;
            return ["error" => 1, "message" => $mailError];
        } else {
            return ["error" => 0, "message" => "success"];
        }
    }
}
