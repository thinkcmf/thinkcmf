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

}