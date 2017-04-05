<?php
/**
 * Created by PhpStorm.
 * User: Powerless
 * Date: 2017-3-13
 * Time: 14:03
 */
namespace app\user\model;

use think\Db;
use think\Model;

class UserModel extends Model
{
    public function doMobile($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('mobile', $user['mobile'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doName($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_login', $user['user_login'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doEmail($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_email', $user['user_email'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function registerEmail($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('user_email', $user['user_email'])->find();
        if (empty($result)) {
            $data = [
                'user_login'      => '',
                'user_email'      => $user['user_email'],
                'mobile'          => '',
                'user_nickname'   => '',
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => 1,
                "user_type"       => 2,
            ];
            $userQuery->insert($data);
            return 0;
        }
        return 1;
    }

    public function registerMobile($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('mobile', $user['mobile'])->find();
        if (empty($result)) {
            $data       = [
                'user_login'      => '',
                'user_email'      => '',
                'mobile'          => $user['mobile'],
                'user_nickname'   => '',
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => 1,
                "user_type"       => 2,
            ];
            $uid        = $userQuery->insertGetId($data);
            $data['id'] = $uid;
            session('user', $data);
            return 0;
        }
        return 1;
    }


    public function resetEmail($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('user_email', $user['user_email'])->find();
        if (!empty($result)) {
            $data = [
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'last_login_time' => time(),
            ];
            $userQuery->where('user_email', $user['user_email'])->update($data);
            return 0;
        }
        return 1;
    }

    public function resetMobile($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('mobile', $user['mobile'])->find();
        if (!empty($result)) {
            $data = [
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'last_login_time' => time(),
            ];
            $userQuery->where('mobile', $user['mobile'])->update($data);
            return 0;
        }
        return 1;
    }

    public function editData($user)
    {
        $uid = cmf_get_current_user_id();
        $user['birthday'] = strtotime($user['birthday']);
        $userQuery = Db::name("user");
        if($userQuery->where('id',$uid)->update($user)){
            $data = $userQuery->where('id',$uid)->find();
            session('user', $data);
            return 1;
        }
        return 0;
    }

    public function editPass($user)
    {
        $uid = cmf_get_current_user_id();
        $password = cmf_password($user['old_password']);
        $userQuery = Db::name("user");
        if($user['password1'] != $user['password2']){
            return 1;
        }
        $pass = $userQuery->where('id',$uid)->find();
        if($pass['user_pass'] != $password){
            return 2;
        }
        $data['user_pass'] = cmf_password($user['password1']);
        if($userQuery->where('id',$uid)->update($data)){
            return 1;
        }
        return 0;
    }
}
