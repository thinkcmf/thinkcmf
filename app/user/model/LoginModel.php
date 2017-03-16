<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-3-13
 * Time: 14:03
 */
namespace app\user\model;

use think\Model;
use think\Db;

class LoginModel extends Model
{
    public function doMobile($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('mobile',$user['mobile'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id',$result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doName($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_login',$user['user_login'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id',$result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doEmail($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_email',$user['user_email'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip' => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }
}
