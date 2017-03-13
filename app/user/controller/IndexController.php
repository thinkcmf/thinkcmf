<?php
namespace app\user\controller;

use cmf\controller\UserBaseController;

class IndexController extends UserBaseController
{

    // 前台用户首页 (公开)
    public function index()
    {

        $id = I("get.id", 0, 'intval');

        $users_model = M("Users");

        $user = $users_model->where(["id" => $id])->find();

        if (empty($user)) {
            $this->error("查无此人！");
        }

        $this->assign($user);
        $this->display(":index");

    }

    // 前台ajax 判断用户登录状态接口
    function is_login()
    {
        if (sp_is_user_login()) {
            $this->ajaxReturn(["status" => 1, "user" => sp_get_current_user()]);
        } else {
            $this->ajaxReturn(["status" => 0, "info" => "此用户未登录！"]);
        }
    }

    //退出
    public function logout()
    {
        $ucenter_syn   = C("UCENTER_ENABLED");
        $login_success = false;
        if ($ucenter_syn) {
            include UC_CLIENT_ROOT . "client.php";
            echo uc_user_synlogout();
        }
        session("user", null);//只有前台用户退出
        redirect(__ROOT__ . "/");
    }

}
