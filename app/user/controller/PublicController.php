<?php
/**
 * Created by PhpStorm.
 * User: Powerless
 * Date: 17/03/31
 * Time: 14:46
 */

namespace app\user\controller;

use cmf\controller\HomeBaseController;

class PublicController extends HomeBaseController
{

    // 用户头像api
    public function avatar()
    {
        $users_model = M("Users");
        $id          = I("get.id", 0, "intval");

        $find_user = $users_model->field('avatar')->where(["id" => $id])->find();

        $avatar              = $find_user['avatar'];
        $avatar              = preg_replace("/^avatar\//", '', $avatar);//2.2以后头像路径统一以avatar/开头
        $should_show_default = false;

        if (empty($avatar)) {
            $should_show_default = true;
        } else {
            if (strpos($avatar, "http") === 0) {
                header("Location: $avatar");
                exit();
            } else {
                $avatar_dir = C("UPLOADPATH") . "avatar/";
                $avatar     = $avatar_dir . $avatar;
                if (file_exists($avatar)) {
                    $imageInfo = getimagesize($avatar);
                    if ($imageInfo !== false) {
                        $fp        = fopen($avatar, "r");
                        $file_size = filesize($avatar);
                        $mime      = $imageInfo['mime'];
                        header("Content-type: $mime");
                        header("Accept-Length:" . $file_size);
                        $buffer     = 1024 * 64;
                        $file_count = 0;
                        //向浏览器返回数据
                        while (!feof($fp) && $file_count < $file_size) {
                            $file_content = fread($fp, $buffer);
                            $file_count += $buffer;
                            echo $file_content;
                            flush();
                            ob_flush();
                        }
                        fclose($fp);
                    } else {
                        $should_show_default = true;
                    }
                } else {
                    $should_show_default = true;
                }
            }


        }

        if ($should_show_default) {
            $imageInfo = getimagesize("public/images/headicon.png");
            if ($imageInfo !== false) {
                $mime = $imageInfo['mime'];
                header("Content-type: $mime");
                echo file_get_contents("public/images/headicon.png");
            }

        }
        exit();

    }

    /**
     * 验证码发送
     */
    public function sendCode()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $code = cmf_get_verification_code($data['username']);
            if (empty($code)) {
                $this->error("验证码发送过多,请明天再试!");
            }
            $validate = new Validate();
            $code = rand(100000,999999);
            if ($validate::is($data['username'], 'email')) {

                cmf_verification_code_log($data['username'],$code);

            } else if (preg_match('/(^(13\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/', $data['username'])) {

                cmf_verification_code_log($data['username'],$code);
            }
            $this->success("验证码已经发送成功!");
        } else {
            $this->error("请求错误");
        }
    }
}
