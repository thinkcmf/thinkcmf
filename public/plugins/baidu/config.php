<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return [
    'username'                 => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title'   => 'username', // 表单的label标题
        'type'    => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value'   => '',// 表单的默认值
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => 'username不能为空'
        ],
        'tip'     => '<a href="https://tongji.baidu.com" target="_blank">马上获取</a>,百度统计' //表单的帮助提示
    ],
    'password'                 => [// 在后台插件配置表单中的键名 ,会是config[password]
        'title'   => 'password',
        'type'    => 'text',
        'value'   => '',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => 'password不能为空'
        ],
        'tip'     => ''
    ],
    'TOKEN'                  => [// 在后台插件配置表单中的键名 ,会是config[select]
        'title'   => 'TOKEN',
        'type'    => 'text',

        'value'   => '',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => 'TOKEN不能为空'
        ],
        'tip'     => ''
    ],
    'siteid'                    => [
        'title'   => '站点ID',
        'type'    => 'text',
        'value'   => '',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '站点ID不能为空'
        ],
        'tip'     => ''
    ],

];
					