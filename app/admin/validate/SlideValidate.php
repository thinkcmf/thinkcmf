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

class SlideValidate extends Validate
{
    protected $rule = [
        'name'    => 'require',
        'name_id' => 'require',
    ];

    protected $message = [
        'name.require'    => '分类名称必须',
        'name_id.require' => '分类标识必须',
    ];

    protected $scene = [
        'add'  => ['name', 'name_id'],
        'edit' => ['name', 'name_id'],
    ];
}