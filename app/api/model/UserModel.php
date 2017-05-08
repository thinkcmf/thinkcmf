<?php
namespace app\api\model;

use think\Model;
use think\Db;   // 调用数据库


class UserModel extends Model
{

    public function getUserbyId($name = 1)
    {
        return (Db::name('user')->where('id',$name)->find());
    }

    /**
     * 登录
     * @param $name
     * @param $pwd
     * @return array|false|\PDOStatement|string|Model
     */
    public function login($name,$pwd)
    {
        return (Db::name('user')->where('user_login',$name)
            ->where('user_pass',$pwd)
            ->find());
    }
}