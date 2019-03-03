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

use app\admin\model\RouteModel;
use think\Validate;

class SettingSiteValidate extends Validate
{
    protected $rule = [
        'options.site_name'             => 'require',
        'admin_settings.admin_password' => 'alphaNum|checkAlias'
    ];

    protected $message = [
        'options.site_name.require'                => '网站名称不能为空',
        'admin_settings.admin_password.alphaNum'   => '后台加密码只能是英文字母和数字',
        'admin_settings.admin_password.checkAlias' => '此加密码不能使用!',
    ];


    // 自定义验证规则
    protected function checkAlias($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        if(preg_match('/^\d+$/',$value)){
            return "加密码不能是纯数字！";
        }

        $routeModel = new RouteModel();
        $fullUrl    = $routeModel->buildFullUrl('admin/Index/index', []);
        if (!$routeModel->existsRoute($value.'$', $fullUrl)) {
            return true;
        } else {
            return "URL规则已经存在,无法设置此加密码!";
        }

    }

}