<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace plugins\wxapp\validate;

use think\Validate;

class AdminWxappValidate extends Validate
{
    protected $rule = [
        // 用|分开
        'name'       => 'require',
        'app_id'     => 'require',
        'app_secret' => 'require'
    ];

    protected $message = [
        'name.require'       => "小程序名称不能为空！",
        'app_id.require'     => "小程序App Id不能为空!",
        'app_secret.require' => '小程序App Secret不能为空!'
    ];


}