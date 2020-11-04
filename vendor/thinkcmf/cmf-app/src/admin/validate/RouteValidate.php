<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class RouteValidate extends Validate
{
    protected $rule = [
        'url'      => 'require|checkUrl',
        'full_url' => 'require|checkFullUrl',
    ];

    protected $message = [
        'url.require'      => '显示网址不能为空',
        'full_url.require' => '原始网址不能为空',
    ];

    // 自定义验证规则
    protected function checkUrl($value, $rule, $data)
    {
        $value = htmlspecialchars_decode($value);
        if (preg_match("/[()'\";]/", $value)) {
            return "显示网址格式不正确!";
        }

        return true;
    }

    // 自定义验证规则
    protected function checkFullUrl($value, $rule, $data)
    {
        $value = htmlspecialchars_decode($value);
        if (preg_match("/[()'\";]/", $value)) {
            return "原始网址格式不正确!";
        }

        return true;
    }

}