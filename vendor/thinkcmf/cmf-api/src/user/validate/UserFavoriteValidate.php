<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// | Date: 2019/01/11
// | Time:下午 03:24
// +----------------------------------------------------------------------


namespace api\user\validate;


use think\Validate;

class UserFavoriteValidate extends Validate
{
    protected $rule    = [
        'object_id'  => 'require',
        'table_name' => 'require',
        'url'        => 'require',
        'title'      => 'require'
    ];
    protected $message = [
        'object_id.require'  => '请填写内容ID',
        'table_name.require' => '请填写内容ID所在表名不带前缀',
        'url.require'        => '请填写内容url',
        'title.require'      => '请填写内容标题'
    ];

    protected $scene = [

    ];
}