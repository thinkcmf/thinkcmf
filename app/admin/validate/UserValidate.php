<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'user_login' => 'require|unique:user',
        'user_pass'  => 'require',
        'user_email' => 'require|email',
    ];
    protected $message = [
        'user_login.require' => '用户不能为空',
        'user_login.unique'  => '用户已存在',
        'user_pass.require'  => '密码不能为空',
        'user_email.require' => '邮箱不能为空',
        'user_email.email'   => '邮箱不正确',
    ];

    protected $scene = [
        'add'  => ['user_login,user_pass,user_email'],
        'edit' => ['user_login,user_email'],
    ];
}