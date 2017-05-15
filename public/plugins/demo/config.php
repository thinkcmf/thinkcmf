<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return [
    'text'     => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '文本', // 表单的label标题
        'type'  => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => 'hello,ThinkCMF!',// 表单的默认值
        'tip'   => '这是文本组件的演示' //表单的帮助提示
    ],
    'password' => [// 在后台插件配置表单中的键名 ,会是config[password]
        'title' => '密码',
        'type'  => 'password',
        'value' => '',
        'tip'   => '这是密码组件'
    ],
    'select'   => [// 在后台插件配置表单中的键名 ,会是config[select]
        'title'   => '下拉列表',
        'type'    => 'select',
        'options' => [//select 和radio,checkbox的子选项
            '1' => 'ThinkCMFX',// 值=>显示
            '2' => 'ThinkCMF',
            '3' => '跟猫玩糗事',
            '4' => '门户应用'
        ],
        'value'   => '1',
        'tip'     => '这是下拉列表组件'
    ],
    'checkbox' => [
        'title'   => '多选框',
        'type'    => 'checkbox',
        'options' => [
            '1' => 'genmaowan.com',
            '2' => 'www.thinkcmf.com'
        ],
        'value'   => 1,
        'tip'     => '这是多选框组件'
    ],
    'radio'    => [
        'title'   => '单选框',
        'type'    => 'radio',
        'options' => [
            '1' => 'ThinkCMFX',
            '2' => 'ThinkCMF'
        ],
        'value'   => '1',
        'tip'     => '这是单选框组件'
    ],
    'radio2'   => [
        'title'   => '单选框2',
        'type'    => 'radio',
        'options' => [
            '1' => 'ThinkCMFX',
            '2' => 'ThinkCMF'
        ],
        'value'   => '1',
        'tip'     => '这是单选框组件2'
    ],
    'textarea' => [
        'title' => '多行文本',
        'type'  => 'textarea',
        'value' => '这里是你要填写的内容',
        'tip'   => '这是多行文本组件'
    ]
];
					