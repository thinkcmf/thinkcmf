<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\portal\validate;

use app\admin\model\RouteModel;
use think\Validate;

class AdminPageValidate extends Validate
{
    protected $rule = [
        'post_title' => 'require',
        'post_alias' => 'checkAlias'
    ];
    protected $message = [
        'post_title.require' => '页面标题不能为空',
    ];

    protected $scene = [
//        'add'  => ['user_login,user_pass,user_email'],
//        'edit' => ['user_login,user_email'],
    ];

    // 自定义验证规则
    protected function checkAlias($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        $routeModel = new RouteModel();
        $fullUrl    = $routeModel->buildFullUrl('portal/Page/index', ['id' => $data['id']]);
        if (!$routeModel->exists($value, $fullUrl)) {
            return true;
        } else {
            return "别名已经存在!";
        }

    }
}